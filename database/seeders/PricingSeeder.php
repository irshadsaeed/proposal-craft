<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PricingPlan;

class PricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run()
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Free',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'yearly_saving' => 0,
                'sort_order' => 1,
                'features' => ['3 active proposals', '5 starter templates', 'Client e-signature', 'PDF export']
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'monthly_price' => 1900,
                'yearly_price' => 1300,
                'yearly_saving' => 7200,
                'sort_order' => 2,
                'features' => ['Unlimited proposals', 'Premium templates', 'Remove branding', 'Stripe payments']
            ],
            [
                'slug' => 'agency',
                'name' => 'Agency',
                'monthly_price' => 4900,
                'yearly_price' => 3400,
                'yearly_saving' => 18000,
                'sort_order' => 3,
                'features' => ['Everything in Pro', 'Team seats', 'Custom domain', 'API access']
            ],
        ];

        foreach ($plans as $p) {
            $features = $p['features'];
            unset($p['features']);

            $plan = PricingPlan::create($p);

            foreach ($features as $f) {
                $plan->features()->create(['text' => $f]);
            }
        }
    }
}
