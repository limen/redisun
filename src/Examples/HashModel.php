<?php
/**
 * @author LI Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/12/7 14:33
 */

namespace Limen\RedModel\Examples;


use Limen\RedModel\Model;

class HashModel extends Model
{
    protected $key = 'redmodel:{id}:hash';

    protected $type = 'hash';
}