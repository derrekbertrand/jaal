<?php

namespace DialInno\Jaal\Rql;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Lexer;
use Xiag\Rql\Parser\Parser;
use Xiag\Rql\Parser\Glob;
use Xiag\Rql\Parser\Node\AbstractQueryNode;


class RqlBuilder {
    protected $query = null;

    public static function append(Builder $query, string $rql)
    {
        //lex and parse
        //todo: check for exceptions
        $lexer = new Lexer();
        $parser = new Parser();
        //get the parsed rql
        $parsed_rql = $parser->parse($lexer->tokenize($rql));

        //make a new Rql Parser, have it visit all nodes
        $that = new static;
        $that->query = clone $query;
        $that->visit($parsed_rql);

        return $that;
    }

    protected function visit(Query $rql)
    {
        //todo: select nodes?

        //query nodes
        if($rql->getQuery() !== null)
            $this->visitQueryNode($rql->getQuery(), $this->query, false);

        //todo: sort nodes?

        //todo: limit nodes?
    }

    protected function visitQueryNode(AbstractQueryNode $node, Builder $query, $invert)
    {
        if($node instanceof \Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode)
            $this->visitScalarNode($node, $query, $invert);
        elseif($node instanceof \Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode)
            $this->visitArrayNode($node, $query, $invert);
        elseif($node instanceof \Xiag\Rql\Parser\Node\Query\AbstractLogicalOperatorNode)
            $this->visitLogicalNode($node, $query, $invert);
        else
            throw new \LogicException('Unknown query node '.$node->getNodeName());
    }

    protected function visitScalarNode(\Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode $node, Builder $query, $invert)
    {
        static $ops = [
            'like'     => 'like',
            'not like' => 'not like',
            'eq'       => '=',
            'ne'       => '<>',
            'lt'       => '<',
            'gt'       => '>',
            'le'       => '<=',
            'ge'       => '>='
        ];
        static $negate_ops = [
            'like'     => 'not like',
            'not like' => 'like',
            'eq'       => '<>',
            'ne'       => '=',
            'lt'       => '>=',
            'gt'       => '<=',
            'le'       => '>',
            'ge'       => '<'
        ];
        $op = $node->getNodeName();
        $val = $node->getValue();
        $field = $node->getField();

        //determine operation or fail
        if($invert && array_key_exists($op, $negate_ops))
            $op = $negate_ops[$op];
        elseif(array_key_exists($op, $ops))
            $op = $ops[$op];
        else
            throw new \LogicException('Unknown scalar query node '.$node->getNodeName());

        //convert globs to likes, and DT to Carbon
        if($val instanceof Glob)
            $val = $val->toLike();
        elseif($val instanceof \DateTimeInterface)
            $val = Carbon::instance($val);

        //add the where clause
        $query = $query->where($field, $op, $val);
    }

    protected function visitArrayNode(\Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode $node, Builder $query, $invert)
    {
        static $negate_ops = [
            'in' => 'out',
            'out' => 'in'
        ];
        $op = $node->getNodeName();

        //check for negation
        if($invert && array_key_exists($op, $negate_op))
            $op = $negate_ops[$op];

        if($op === 'in')
            $query->whereIn($node->getField(), $node->getValues());
        elseif($op === 'out')
            $query->whereNotIn($node->getField(), $node->getValues());
        else
            throw new \LogicException('Unknown array query node '.$op);
    }

    protected function visitLogicalNode(\Xiag\Rql\Parser\Node\Query\AbstractLogicalOperatorNode $node, Builder $query, $invert)
    {
        //if NOT, everything in this block is negated
        if($node->getNodeName() === 'not')
            $invert = !$invert;

        //everything joined by OR
        if($node->getNodeName() === 'or')
            $query->where(function($q) use ($node, $invert) {
                //run the first one like normal
                $node_queries = $node->getQueries();
                $inner_node = array_shift($node_queries);
                $this->visitQueryNode($inner_node, $q, $invert);

                //wrap the others in an OR
                foreach($node_queries as $inner_node)
                    $q->orWhere(function($q) use ($inner_node, $invert) {
                        $this->visitQueryNode($inner_node, $q, $invert);
                    });
            });
        //everything is joined by AND
        elseif(($node->getNodeName() === 'and') || ($node->getNodeName() === 'not'))
            $query->where(function($q) use ($node, $invert) {
                foreach($node->getQueries() as $inner_node)
                    $this->visitQueryNode($inner_node, $q, $invert);
            });
        else
            throw new \LogicException('Unknown logical query node '.$node->getNodeName());
    }

    public function getBuilder()
    {
        return $this->query;
    }
}
