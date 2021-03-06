<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */
 
class ClawbotFrame
{
    private $viewArr;

    public function __construct(array $viewArr)
    {
        $this->viewArr              = $viewArr;
    }

    public function render()
    {
        $viewArray                  = array();
        foreach ($this->viewArr as $view) {
            $viewArray[]            = $view->render();
        }

        ob_start();
        require TEMPLATES_DIR . '/clawbotframeview_template.php';
        $output = ob_get_contents();
      	ob_end_clean();

        return $output;
    }
}