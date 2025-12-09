<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

final class ExportProgressBar
{
    private ?ProgressBar $bar = null;
    private int $total = 0;
    private float $startedAt = 0.0;

    public function __construct( private readonly ?OutputInterface $output = null,) {}

    public function start(int $total): void
    {
        $this->total = max($total, 1);
        $this->startedAt = microtime(true);

        if (! $this->output) {
            // Rodando fora do console (ex: HTTP, job sem output):
            // simplesmente não desenha a barra.
            return;
        }

        // Define placeholder de memória
        ProgressBar::setPlaceholderFormatterDefinition('memory', function (ProgressBar $bar): string {
            return $this->formatMemory(memory_get_usage(true));
        });

        $this->bar = new ProgressBar($this->output, $this->total);

        // Formato:
        //  - current/max (registros)
        //  - barra gráfica
        //  - percentual
        //  - tempo decorrido
        //  - tempo estimado total
        //  - memória usada
        $this->bar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% ' .
                '| Elapsed: %elapsed:6s% ' .
                '| Est: %estimated:-6s% ' .
                '| Mem: %memory:6s%'
        );

        $this->bar->start();
    }

    public function advance(int $step = 1): void
    {
        if ($this->bar) {
            $this->bar->advance($step);
        }
    }

    public function advanceTo(int $current): void
    {
        if (! $this->bar) {
            return;
        }

        $delta = $current - $this->bar->getProgress();
        if ($delta > 0) {
            $this->bar->advance($delta);
        }
    }

    public function finish(): void
    {
        if ($this->bar) {
            $this->bar->finish();
            $this->output?->writeln('');
        }
    }

    public function getElapsedSeconds(): float
    {
        return microtime(true) - $this->startedAt;
    }

    private function formatMemory(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return sprintf('%.1f %s', $bytes, $units[$i]);
    }
}
