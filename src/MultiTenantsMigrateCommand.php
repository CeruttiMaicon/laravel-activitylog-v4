<?php

namespace Spatie\Activitylog;

use Illuminate\Console\Command;

class MultiTenantsMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'multi_tenants:migrate {--path= : The path of migrations files to be executed} {--tenants= : The tenants to be migrated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for tenant(s)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenantModel = config('activitylog.tenant_model');
        $tenants = $tenantModel::whereNot('id', 'like', '%_logs')->get();

        if ($this->option('tenants')) {
            // NOTE - If the --tenants parameter was passed, there should be no option with _logs suffix
            if (strpos($this->option('tenants'), '_logs') !== false) {
                $this->error('Invalid tenant name. Tenant name cannot have _logs suffix.');
            }

            $tenants = $tenants->whereIn('id', $this->option('tenants'));

            if ($tenants->count() == 0) {
                $this->error('Invalid tenant name. Tenant name does not exist.');
            }
        }

        $path = $this->option('path') == 'base' ? 'database/migrations/tenant/base' : 'database/migrations/tenant/releases';

        foreach ($tenants as $tenant) {
            $this->call('tenants:migrate', [
                '--tenants' => $tenant->id,
                '--path' => $path,
            ]);
        }

        return 0;
    }
}
