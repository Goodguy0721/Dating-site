<?php

namespace Pg\Libraries\View\Driver\Twig\Tokens\Helper;

class Parser extends \Twig_TokenParser
{
    private $params = array();

    public function parse(\Twig_Token $token)
    {
        $var = null;
        $module = null;

        $this->params = array();

        $stream = $this->parser->getStream();

        if ($stream->nextIf(\Twig_Token::PUNCTUATION_TYPE, '{')) {
            $helper = $this->parser->getExpressionParser()->parseExpression();
            $stream->expect(\Twig_Token::PUNCTUATION_TYPE, '}');
        } else {
            $helper = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
            if ($stream->nextIf(\Twig_Token::OPERATOR_TYPE, '=')) {
                $var = $helper;
                $helper = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
            }
        }

        if ($stream->nextIf(\Twig_Token::PUNCTUATION_TYPE, ':')) {
            $module = $helper;
            $helper = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
        }

        $stream->expect(\Twig_Token::PUNCTUATION_TYPE, '.');

        if ($stream->nextIf(\Twig_Token::PUNCTUATION_TYPE, '{')) {
            $function = $this->parser->getExpressionParser()->parseExpression();
            $stream->expect(\Twig_Token::PUNCTUATION_TYPE, '}');
        } else {
            $function = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
        }

        $stream->expect(\Twig_Token::PUNCTUATION_TYPE, '(');
        $this->parseParams();
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $node = new Node($module, $helper, $function, $this->params, $var, $token->getLine(), $this->getTag());

        return $node;
    }

    private function getHelper($helper, $function, $params = null)
    {
        $ci = get_instance();
        $ci->load->helper($helper);
        if (function_exists($function)) {
            $result = call_user_func_array($function, $params);

            return $result;
        } else {
            return '';
        }
    }

    private function callHelper($helper, $function, $params = null)
    {
        $ci = get_instance();
        $ci->load->helper($helper);
        if (function_exists($function)) {
            ob_start();
            $result = call_user_func_array($function, $params);
            $output_buffer = ob_get_contents();
            ob_end_clean();

            return $output_buffer . $result;
        } else {
            return '';
        }
    }

    private function parseParams()
    {
        $stream = $this->parser->getStream();

        if ($stream->nextIf(\Twig_Token::PUNCTUATION_TYPE, ')')) {
            return false;
        } elseif (is_null($this->params)) {
            $this->params = array();
        }

        $params_expr = $this->parser->getExpressionParser()->parseExpression();

        $this->params[] = $params_expr;

        if ($stream->nextIf(\Twig_Token::PUNCTUATION_TYPE, ',')) {
            $this->parseParams($stream);
        } else {
            $stream->expect(\Twig_Token::PUNCTUATION_TYPE, ')');

            return true;
        }
    }

    public function getTag()
    {
        return 'helper';
    }
}
