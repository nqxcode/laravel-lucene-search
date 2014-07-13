<?php  namespace unit;

class HelpersTest extends \TestCase
{
    public function testRmDirRecursive()
    {
        $dir = $this->createTempDir();

        rmdir_recursive($dir);
        $this->assertFalse(is_dir($dir));
    }

    public function testEscape()
    {
        $str = lucene_query_escape("+ - && || ! ( ) { } [ ] ^ \" ~ * ? : \\");
        $this->assertEquals("\\+ \\- \\&& \\|| \\! \\( \\) \\{ \\} \\[ \\] \\^ \\\" \\~ \\* \\? \\: \\\\", $str);
    }

    private function createTempDir()
    {
        $root = sys_get_temp_dir();

        $dir = $root . '/temp_dir_' . uniqid();

        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if (!is_dir($dir . '/temp_dir')) {
            mkdir($dir . '/temp_dir');
            if (!file_exists($dir . '/temp_dir/temp_file')) {
                touch($dir . '/temp_dir/temp_file');
            }
        }
        return $dir;
    }
}
