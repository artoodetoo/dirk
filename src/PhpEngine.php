<?php

namespace R2\Templating;

class PhpEngine
{
    protected $views;
    protected $ext;
    protected $blocks;
    protected $blockStack;

    /**
     * Constructor
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->views = isset($config['views']) ? $config['views'] : '.';
        $this->ext   = isset($config['ext'])   ? $config['ext']   : '.php';
        $this->blocks = [];
        $this->blockStack = [];
    }

    /**
     * Prepare file to include
     * @param  string $name
     * @return string
     */
    protected function prepare($name)
    {
        return $this->views.'/'.$name.$this->ext;
    }


    /**
     * Print result of templating
     * @param string $name
     * @param array  $data
     */
    public function render($name, array $data = [])
    {
        echo $this->fetch($name, $data);
    }

    /**
     * Return result of templating
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    public function fetch($name, array $data = [])
    {
        $this->templates[] = $name;
        if (!empty($data)) {
            extract($data);
        }
        while ($_name = array_shift($this->templates)) {
            $this->beginBlock('content');
            require($this->prepare($_name));
            $this->endBlock();
        }
        return $this->block('content');
    }

    /**
     * Is template file exists?
     * @param  string  $name
     * @return Boolean
     */
    public function exists($name)
    {
        return file_exists($this->prepare($name));
    }

    /**
     * Define parent
     * @param string $name
     */
    protected function extend($name)
    {
        $this->templates[] = $name;
    }

    /**
     * Return content of block if exists
     * @param  string $name
     * @param  string $default
     * @return string
     */
    protected function block($name, $default = '')
    {
        return array_key_exists($name, $this->blocks)
            ? $this->blocks[$name]
            : $default;
    }

    /**
     * Block begins
     * @param string $name
     */
    protected function beginBlock($name)
    {
        array_push($this->blockStack, $name);
        ob_start();
    }

    /**
     * Block ends
     * @param boolean $overwrite
     * @return string
     */
    protected function endBlock($overwrite = false)
    {
        $name = array_pop($this->blockStack);
        if ($overwrite || !array_key_exists($name, $this->blocks)) {
            $this->blocks[$name] = ob_get_clean();
        } else {
            $this->blocks[$name] .= ob_get_clean();
        }
        return $name;
    }
}
