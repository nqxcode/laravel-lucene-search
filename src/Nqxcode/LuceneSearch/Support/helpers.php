<?php
if (!function_exists('rmdir_recursive')) {
    /**
     * Recursively remove an directory.
     *
     * @param string $path
     *
     * @return bool
     */
    function rmdir_recursive($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                rmdir_recursive(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else {
            if (is_file($path) === true) {
                return unlink($path);
            }
        }

        return false;
    }
}
