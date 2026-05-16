<?php

namespace Tests;

use App\Models\Module as ModuleModel;
use App\Support\Modules\DatabaseActivator;
use Illuminate\Support\Facades\Cache;
use Nwidart\Modules\Module as NwidartModule;

class DatabaseActivatorCacheTest extends TestCase
{
    public function test_set_active_invalidates_nwidart_modules_cache(): void
    {
        $name = 'PhpvmsActivatorCacheFixture';
        ModuleModel::create(['name' => $name, 'enabled' => false]);

        $key = config('modules.cache.key');
        $driver = config('modules.cache.driver');
        Cache::store($driver)->put($key, ['stale-marker' => true], 3600);

        $nwidartModule = $this->getMockBuilder(NwidartModule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['registerAliases', 'registerProviders', 'getCachedServicesPath', 'getName'])
            ->getMock();
        $nwidartModule->method('getName')->willReturn($name);

        $activator = app(DatabaseActivator::class);
        $activator->enable($nwidartModule);

        $this->assertNull(
            Cache::store($driver)->get($key),
            'Expected nWidart modules cache to be invalidated after enable().'
        );
    }

    public function test_set_active_by_name_invalidates_nwidart_modules_cache(): void
    {
        $name = 'PhpvmsActivatorCacheFixtureByName';
        ModuleModel::create(['name' => $name, 'enabled' => true]);

        $key = config('modules.cache.key');
        $driver = config('modules.cache.driver');
        Cache::store($driver)->put($key, ['stale-marker' => true], 3600);

        $activator = app(DatabaseActivator::class);
        $activator->setActiveByName($name, false);

        $this->assertNull(
            Cache::store($driver)->get($key),
            'Expected nWidart modules cache to be invalidated after setActiveByName().'
        );
    }

    public function test_delete_invalidates_nwidart_modules_cache(): void
    {
        $name = 'PhpvmsActivatorCacheFixtureDelete';
        ModuleModel::create(['name' => $name, 'enabled' => true]);

        $key = config('modules.cache.key');
        $driver = config('modules.cache.driver');
        Cache::store($driver)->put($key, ['stale-marker' => true], 3600);

        $nwidartModule = $this->getMockBuilder(NwidartModule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['registerAliases', 'registerProviders', 'getCachedServicesPath', 'getName'])
            ->getMock();
        $nwidartModule->method('getName')->willReturn($name);

        $activator = app(DatabaseActivator::class);
        $activator->delete($nwidartModule);

        $this->assertNull(
            Cache::store($driver)->get($key),
            'Expected nWidart modules cache to be invalidated after delete().'
        );
    }
}
