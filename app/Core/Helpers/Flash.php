<?php

namespace Flex\Core\Helpers;

class Flash
{
    public static function success(string $message): void
    {
        $_SESSION['flash_success'] = $message;
    }

    public static function error(string $message): void
    {
        $_SESSION['flash_error'] = $message;
    }

    public static function warning(string $message): void
    {
        $_SESSION['flash_warning'] = $message;
    }

    public static function render(): string
    {
        $output = '';
        $types = [
            'success' => ['bg' => 'bg-emerald-500', 'icon' => 'fa-check-circle'],
            'error'   => ['bg' => 'bg-rose-500',   'icon' => 'fa-times-circle'],
            'warning' => ['bg' => 'bg-amber-500',  'icon' => 'fa-exclamation-circle']
        ];

        foreach ($types as $type => $styles) {
            $key = "flash_{$type}";
            if (isset($_SESSION[$key])) {
                $message = $_SESSION[$key];
                $uniqueId = 'flash_' . $type . '_' . time();
                
                $output .= "
                <div x-data=\"alertComponent('{$uniqueId}', 1)\" 
                    x-show=\"visible\"
                    x-init=\"setTimeout(() => visible = false, 5000)\"
                    
                    x-transition:enter=\"transition ease-out duration-500\"
                    x-transition:enter-start=\"opacity-0 translate-x-10\"
                    x-transition:enter-end=\"opacity-100 translate-x-0\"
                    
                    x-transition:leave=\"transition ease-in duration-300\"
                    x-transition:leave-start=\"opacity-100 translate-x-0\"
                    x-transition:leave-end=\"opacity-0 translate-x-10\"
                    
                    class=\"fixed top-5 right-5 z-[9999] flex items-center p-4 text-white rounded-lg shadow-2xl {$styles['bg']} max-w-sm w-full\">
                    <i class=\"fas {$styles['icon']} text-xl mr-3\"></i>
                    <span class=\"font-medium flex-1\">{$message}</span>
                    <button @click=\"close()\" class=\"ml-3 hover:text-white/80 transition-colors\">
                        <i class=\"fas fa-times\"></i>
                    </button>
                </div>";
                
                unset($_SESSION[$key]);
            }
        }

        return $output;
    }
}