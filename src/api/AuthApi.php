<?php
declare(strict_types=1);

global $apiAuth, $method;

$input = api_input();

if ($subRes1 === null) {
    api_response(404, false, 'Endpoint auth tidak valid');
}

if ($subRes1 === 'login' && $method === 'POST') {
    $identifier = trim((string)($input['login'] ?? $input['email'] ?? $input['nip'] ?? $input['username'] ?? ''));
    $password   = (string)($input['password'] ?? '');

    if ($identifier === '' || $password === '') {
        api_response(422, false, 'NIP / Username / Email dan password wajib diisi');
    }

    $device = $input['device_name'] ?? 'Mobile App';
    $result = $apiAuth->attempt($identifier, $password, $device);
    if (!$result) {
        api_response(401, false, 'Kredensial login atau password salah, atau akun dinonaktifkan');
    }
    api_response(200, true, 'Login berhasil', $result);
}

if ($subRes1 === 'logout' && $method === 'POST') {
    $apiAuth->require();
    $token = $apiAuth->token();
    if ($token) $apiAuth->revokeToken($token);
    api_response(200, true, 'Logout berhasil');
}

if ($subRes1 === 'me' && $method === 'GET') {
    $user = $apiAuth->require();
    api_response(200, true, 'Data profil', $user);
}

api_response(405, false, 'Method tidak diizinkan untuk endpoint ini');
