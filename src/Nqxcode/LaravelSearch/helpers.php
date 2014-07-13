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

if (!function_exists('lucene_query_escape')) {
    /**
     * Escape special characters for Lucene query.
     *
     * @param string $str
     *
     * @return string
     */
    function lucene_query_escape($str)
    {
        $special_chars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':'];

        foreach ($special_chars as $ch) {
            $str = str_replace($ch, "\\{$ch}", $str); // escape all special characters
        }

        $str = str_ireplace([' and ', ' or ', ' not ', ' to '], '', $str); // remove other operators

        return $str;
    }
}
