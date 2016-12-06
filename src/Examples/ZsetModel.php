<?php
/**
 * @author LI Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/12/7 14:34
 */

namespace Limen\RedModel\Examples;


use Limen\RedModel\Model;

class ZsetModel extends Model
{
    protected $type = 'zset';

    protected $key = 'redmodel:{id}:zset';
}