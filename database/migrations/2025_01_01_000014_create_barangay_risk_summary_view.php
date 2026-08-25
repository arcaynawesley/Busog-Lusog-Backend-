<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<SQL
            create or replace view barangay_risk_summary as
            select
              b.id as barangay_id,
              b.name as barangay_name,
              b.latitude,
              b.longitude,
              count(distinct c.id) as total_children,
              count(distinct c.id) filter (where rp.risk_level = 'very_high') as very_high_risk,
              count(distinct c.id) filter (where rp.risk_level = 'high') as high_risk,
              count(distinct c.id) filter (where rp.risk_level = 'moderate') as moderate_risk,
              count(distinct c.id) filter (where rp.risk_level = 'low') as low_risk
            from barangays b
            left join families f on f.barangay_id = b.id
            left join children c on c.family_id = f.id and c.is_active
            left join lateral (
              select risk_level from risk_predictions
              where child_id = c.id
              order by predicted_at desc
              limit 1
            ) rp on true
            group by b.id, b.name, b.latitude, b.longitude
        SQL);
    }

    public function down(): void
    {
        DB::statement('drop view if exists barangay_risk_summary');
    }
};