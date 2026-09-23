<?php

namespace App\Security;

use App\Entity\User;

class LoginRedirectResolver
{
    /**
     * @return array{interface: string, interfaceKey: string, redirectTarget: string, role: string, roles: array<int, string>, permissions: array<int|string, mixed>}
     */
    public function resolveForUser(User $user): array
    {
        $role = $this->normalizeRoleName($user->getRoleName());
        $roles = $user->getRoles();
        $permissions = $user->getUserTypeId()?->getPermission() ?? [];

        [$interfaceKey, $interfaceLabel, $redirectTarget] = match ($role) {
            'SUPER_ADMIN', 'ADMIN' => ['super_admin', 'Super Admin', '/super-admin'],
            'COMPANY' => ['company', 'Company', '/company'],
            'UNIVERSITY' => ['university', 'University', '/university'],
            'STUDENT' => ['student', 'Student', '/student'],
            default => ['home', 'Home', '/'],
        };

        return [
            'interface' => $interfaceLabel,
            'interfaceKey' => $interfaceKey,
            'redirectTarget' => $redirectTarget,
            'role' => $role,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }

    private function normalizeRoleName(?string $roleName): string
    {
        $normalized = strtoupper(trim((string) $roleName));
        if ($normalized === '') {
            return 'USER';
        }

        if (str_starts_with($normalized, 'ROLE_')) {
            return substr($normalized, 5);
        }

        return $normalized;
    }
}
