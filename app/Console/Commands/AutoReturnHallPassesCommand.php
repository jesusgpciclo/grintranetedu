<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HallPass;
use Carbon\Carbon;

class AutoReturnHallPassesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salidas:auto-return {--date= : Fecha específica en formato YYYY-MM-DD}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finaliza automáticamente las salidas de alumnos no regresados al terminar el día.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date');
        $query = HallPass::whereNull('end_time');

        if ($date) {
            $query->whereDate('date', $date);
        } else {
            $query->whereDate('date', '<=', now()->toDateString());
        }

        $passes = $query->get();
        $count = $passes->count();

        foreach ($passes as $pass) {
            // Si la fecha del pase es de un día anterior, cerramos a las 23:59:59 de ese día
            $passDate = Carbon::parse($pass->date)->toDateString();
            $today = now()->toDateString();

            if ($passDate < $today) {
                $closedAt = Carbon::parse($passDate . ' 23:59:59');
            } else {
                $closedAt = now();
            }

            $pass->update(['end_time' => $closedAt]);
        }

        $this->info("Se han finalizado automáticamente {$count} salidas pendientes.");

        return Command::SUCCESS;
    }
}
