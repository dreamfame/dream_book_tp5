<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/20
 * Time: 9:36
 */

namespace app\common\inter;


interface IBase
{
    public function create();
    public function read();
    public function update();
    public function delete();
}