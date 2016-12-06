<?php
/**
 * @author LI Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/12/7 14:35
 */

namespace Limen\RedModel\Examples;


use Limen\RedModel\Model;

class StringModel extends Model
{
    protected $key = 'redmodel:{id}:string:{name}';

    protected $type = 'string';
}