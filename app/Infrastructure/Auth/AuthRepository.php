<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\IAuthRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthRepository implements IAuthRepository
{
    public function authenticate(string $username, string $password, string $platform = 'WEB'): ?User
    {
        $apiUrl = rtrim((string) config('services.api_fert.url', 'http://localhost:8000/api'), '/');

        Log::info('[AuthRepository] Iniciando autenticación mediante API de Fertilización', [
            'usuario' => $username,
            'apiUrl' => "{$apiUrl}/login",
            'platform' => $platform,
        ]);

        try {
            $response = Http::timeout(15)->post("{$apiUrl}/login", [
                'login' => $username,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? $data['token'] ?? null;
                $userData = $data['user'] ?? $data['data'] ?? $data;

                if (! empty($token)) {
                    session(['auth_token' => $token]);
                }

                $id = (int) ($userData['id'] ?? $userData['codigoAsesor'] ?? $userData['id_usuario'] ?? 1);
                $name = (string) (
                    $userData['Name'] ??
                    $userData['name'] ??
                    $userData['SlpName'] ??
                    $userData['slpName'] ??
                    $userData['nombre'] ??
                    $userData['Nombre'] ??
                    $userData['nombreAsesor'] ??
                    $userData['NombreAsesor'] ??
                    $userData['nombre_completo'] ??
                    $userData['full_name'] ??
                    $userData['Asesor'] ??
                    $userData['asesor'] ??
                    ''
                );

                // Si el nombre viene vacío o es un correo, buscar el nombre real en el catálogo de asesores
                if ((empty($name) || str_contains($name, '@')) && ! empty($token)) {
                    try {
                        $searchPrefix = explode('@', $username)[0];
                        $advRes = Http::withToken($token)
                            ->timeout(4)
                            ->get("{$apiUrl}/asesores/buscar", ['nombre' => $searchPrefix]);

                        if ($advRes->successful()) {
                            $advList = $advRes->json('data') ?? $advRes->json('asesores') ?? $advRes->json();
                            if (is_array($advList) && ! empty($advList)) {
                                foreach ($advList as $adv) {
                                    $advEmail = strtolower((string) ($adv['U_correo'] ?? $adv['email'] ?? ''));
                                    $advName = (string) ($adv['SlpName'] ?? $adv['AdvisorName'] ?? $adv['Name'] ?? '');
                                    if ($advEmail === strtolower($username) && ! empty($advName)) {
                                        $name = $advName;
                                        break;
                                    }
                                }
                                if ((empty($name) || str_contains($name, '@')) && ! empty($advList[0]['SlpName'] ?? $advList[0]['Name'])) {
                                    $name = (string) ($advList[0]['SlpName'] ?? $advList[0]['Name']);
                                }
                            }
                        }
                    } catch (\Throwable) {
                        // Continuar si la consulta de catálogo falla
                    }
                }

                // Fallback: si aún es un correo o vacío, formatear humanamente el prefijo del usuario
                if (empty($name) || str_contains($name, '@')) {
                    $prefix = explode('@', $username)[0];
                    $cleanWords = preg_split('/[._-]/', $prefix);
                    $name = ucwords(implode(' ', array_filter($cleanWords ?: [$prefix])));
                }

                $email = (string) ($userData['email'] ?? (str_contains($username, '@') ? $username : $username.'@impulsora.com'));
                $rol = (string) ($userData['rol'] ?? $userData['role'] ?? $userData['rolAsesor'] ?? 'ASESOR');
                $zona = ! empty($userData['zona']) ? (string) $userData['zona'] : (! empty($userData['zonaAsesor']) ? (string) $userData['zonaAsesor'] : null);
                $zonaNombre = ! empty($userData['zonaNombre']) ? (string) $userData['zonaNombre'] : null;

                $user = new User(
                    id: $id,
                    name: $name,
                    email: $email,
                    rol: $rol,
                    activo: true,
                    sessionId: $token ? 'token_'.$token : 'sess_'.md5($username.time()),
                    zona: $zona,
                    zonaNombre: $zonaNombre
                );

                Log::info('[AuthRepository] Usuario autenticado con éxito vía API', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'rol' => $user->rol,
                ]);

                return $user;
            }

            Log::warning('[AuthRepository] Credenciales inválidas devueltas por API', [
                'usuario' => $username,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('[AuthRepository] Excepción al llamar a endpoint de login API', [
                'usuario' => $username,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function logout(string $sessionId): bool
    {
        Log::info('Cierre de sesión de usuario', ['sessionId' => $sessionId]);

        return true;
    }

    public function logAttempt(string $username, bool $success, string $ip, string $platform = 'WEB'): void
    {
        // RN-05: Registrar cada intento
        Log::info('Auditoría de Intento Login', [
            'username' => $username,
            'success' => $success,
            'ip' => $ip,
            'platform' => $platform,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
