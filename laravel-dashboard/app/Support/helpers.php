<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('store_error_log')) {
    /**
     * Persist an error message or exception to the error_log table.
     *
     * @param mixed $error String, array, Throwable, or any value castable to string
     */
    function store_error_log(mixed $error): void
    {
        try {
            $message = $error instanceof Throwable
                ? ($error->getMessage() . "\n" . $error->getTraceAsString())
                : (is_array($error) || is_object($error)
                    ? json_encode($error, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    : (string) $error);

            DB::table('error_log')->insert([
                'error' => $message,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Swallow to avoid cascading failures; optionally log to PHP error log
            error_log('Failed to store error_log: ' . $e->getMessage());
        }
    }
}


