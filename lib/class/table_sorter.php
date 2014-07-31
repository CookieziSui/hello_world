<?php

abstract class Helper_Array
{
    static function removeEmpty(& $arr, $trim = true)
    {
        foreach ($arr as $key => $value) 
        {
            if (is_array($value)) 
            {
                self::removeEmpty($arr[$key]);
            } 
            else 
            {
                $value = trim($value);
                if ($value == '') 
                {
                    unset($arr[$key]);
                } 
                elseif ($trim) 
                {
                    $arr[$key] = $value;
                }
            }
        }
    }


    static function getCols($arr, $col)
    {
        $ret = array();
        foreach ($arr as $row) 
        {
            if (isset($row[$col])) { $ret[] = $row[$col]; }
        }
        return $ret;
    }


    static function toHashmap($arr, $key_field, $value_field = null)
    {
        $ret = array();
        if ($value_field) 
        {
            foreach ($arr as $row) 
            {
                $ret[$row[$key_field]] = $row[$value_field];
            }
        } 
        else 
        {
            foreach ($arr as $row) 
            {
                $ret[$row[$key_field]] = $row;
            }
        }
        return $ret;
    }
    
    static function groupBy($arr, $key_field)
    {
        $ret = array();
        foreach ($arr as $row) 
        {
            $key = $row[$key_field];
            $ret[$key][] = $row;
        }
        return $ret;
    }

    static function toTree($arr, $key_node_id, $key_parent_id = 'parent_id',
                           $key_childrens = 'childrens', & $refs = null)
    {
        $refs = array();
        foreach ($arr as $offset => $row) 
        {
            $arr[$offset][$key_childrens] = array();
            $refs[$row[$key_node_id]] =& $arr[$offset];
        }

        $tree = array();
        foreach ($arr as $offset => $row) 
        {
            $parent_id = $row[$key_parent_id];
            if ($parent_id)
            {
                if (!isset($refs[$parent_id]))
                {
                    $tree[] =& $arr[$offset];
                    continue;
                }
                $parent =& $refs[$parent_id];
                $parent[$key_childrens][] =& $arr[$offset];
            }
            else
            {
                $tree[] =& $arr[$offset];
            }
        }

        return $tree;
    }

    static function treeToArray($tree, $key_childrens = 'childrens')
    {
        $ret = array();
        if (isset($tree[$key_childrens]) && is_array($tree[$key_childrens]))
        {
            $childrens = $tree[$key_childrens];
            unset($tree[$key_childrens]);
            $ret[] = $tree;
            foreach ($childrens as $node) 
            {
                $ret = array_merge($ret, self::treeToArray($node, $key_childrens));
            }
        }
        else
        {
            unset($tree[$key_childrens]);
            $ret[] = $tree;
        }
        return $ret;
    }

    static function sortByCol($array, $keyname, $dir = SORT_ASC)
    {
        return self::sortByMultiCols($array, array($keyname => $dir));
    }

    static function sortByMultiCols($rowset, $args)
    {
        $sortArray = array();
        $sortRule = '';
        foreach ($args as $sortField => $sortDir) 
        {
            foreach ($rowset as $offset => $row) 
            {
                $sortArray[$sortField][$offset] = $row[$sortField];
            }
            $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
        }
        if (empty($sortArray) || empty($sortRule)) { return $rowset; }
        eval('array_multisort(' . $sortRule . '$rowset);');
        return $rowset;
    }
}
