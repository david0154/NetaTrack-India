<?php
/**
 * NetaTrack India — Global AI helper functions
 * Use anywhere in PHP views/controllers.
 *
 * Examples:
 *   ai()->ask("Summarise BJP's 2024 manifesto in 2 sentences");
 *   ai()->summariseLeader($leader);
 *   ai()->classifyNews($title, $desc);
 *   ai()->translateToHindi($text);  // uses Sarvam AI
 */

namespace App\AI;

class AIHelper
{
    private static ?AIRouter $instance = null;

    public static function getInstance(): AIRouter
    {
        if (self::$instance === null) {
            self::$instance = AIRouter::make();
        }
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}

// Global shortcut function — use ai() anywhere
if (!function_exists('ai')) {
    function ai(): \App\AI\AIRouter {
        return \App\AI\AIHelper::getInstance();
    }
}
