<?php

namespace Tests\Unit\Support;

use App\Support\PermissionPolicy;
use PHPUnit\Framework\TestCase;

class PermissionPolicyTest extends TestCase
{
    public function test_tenant_permission_accepts_own_and_all_unit_scope(): void
    {
        $this->assertSame(
            ['own_unit', 'all_units'],
            PermissionPolicy::allowedScopes('news.view')
        );
    }

    public function test_global_permission_requires_all_units(): void
    {
        $this->assertSame(
            ['all_units'],
            PermissionPolicy::allowedScopes('settings.update')
        );

        $this->assertFalse(
            PermissionPolicy::scopeAllowed('settings.update', 'own_unit')
        );
    }

    public function test_unit_mutation_requires_all_units(): void
    {
        $this->assertSame(
            ['own_unit', 'all_units'],
            PermissionPolicy::allowedScopes('units.view')
        );

        $this->assertSame(
            ['all_units'],
            PermissionPolicy::allowedScopes('units.update')
        );
    }

    public function test_user_cannot_review_or_publish_news(): void
    {
        $this->assertTrue(
            PermissionPolicy::systemRoleAllows('user', 'news.create')
        );

        $this->assertFalse(
            PermissionPolicy::systemRoleAllows('user', 'news.review')
        );
        $this->assertFalse(
            PermissionPolicy::systemRoleAllows('user', 'news.publish')
        );
        $this->assertFalse(
            PermissionPolicy::systemRoleAllows('user', 'news.unpublish')
        );
    }

    public function test_unknown_permission_is_fail_closed(): void
    {
        $this->assertSame([], PermissionPolicy::allowedScopes('unknown.action'));
        $this->assertFalse(
            PermissionPolicy::systemRoleAllows('super-admin', 'unknown.action')
        );
    }
}
