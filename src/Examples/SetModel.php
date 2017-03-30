<?php
/**
 * @author LI Mengxiang
 * @email lmx@yiban.cn
 * @since 2017/3/29 16:14
 */

namespace Limen\RedModel\Examples;


class SetModel extends BaseModel
{
    protected $type = 'set';

    protected $key = 'redmodel:set:{id}:members';
}