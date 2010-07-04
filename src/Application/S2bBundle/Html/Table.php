<?php

namespace Application\S2bBundle\Html;

class Table
{
    protected
        $head = array(),
        $body = array(),
        $foot = array(),
        $useStrip = false,
        $stripCount=0;

    public function useStrip($value = null)
    {
        if (null === $value)
        {
            return $this->useStrip;
        }

        $this->useStrip = (bool) $value;
    }

    public function clearBody()
    {
        $this->body = array();
    }

    public function render()
    {
        return '<table>'.$this->renderHead().$this->renderFoot().$this->renderBody().'</table>';
    }

    public function renderHead()
    {
        return $this->renderPart($this->head, 'thead', 'th');
    }

    public function renderBody()
    {
        return $this->renderPart($this->body, 'tbody', 'td');
    }

    public function renderFoot()
    {
        return $this->renderPart($this->foot, 'tfoot', 'th');
    }

    protected function renderPart(array $rows, $partTag, $cellTag)
    {
        if (empty($rows))
        {
            return '';
        }

        $this->stripCount = 0;

        $html = '<'.$partTag.'>';

        foreach($rows as $row)
        {
            $html .= $this->renderRow($row, $cellTag);
        }

        $html .= '</'.$partTag.'>';

        return $html;
    }

    protected function renderRow(array $row, $cellTag)
    {
        if ($this->useStrip && 'td' === $cellTag)
        {
            $open = '<tr class="'.((++$this->stripCount % 2) ? 'even' : 'odd').'">';
        }
        else
        {
            $open = '<tr>';
        }

        return $open.'<'.$cellTag.'>'.implode('</'.$cellTag.'><'.$cellTag.'>', $row).'</'.$cellTag.'></tr>';
    }

    public function head()
    {
        $this->head[] = $this->validateRowArgs(func_get_args());

        return $this;
    }

    public function body()
    {
        $this->body[] = $this->validateRowArgs(func_get_args());

        return $this;
    }

    public function foot()
    {
        $this->foot[] = $this->validateRowArgs(func_get_args());

        return $this;
    }

    protected function validateRowArgs($args)
    {
        if(1 == count($args))
        {
            $args = (array) $args[0];
        }

        foreach($args as $index => $arg)
        {
            $args[$index] = (string) $arg;
        }

        return $args;
    }

    public function __toString()
    {
        return $this->render();
    }
}
