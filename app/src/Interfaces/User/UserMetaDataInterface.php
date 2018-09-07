<?php
namespace TriTan\Interfaces\User;

interface UserMetaDataInterface
{
    public function create($id, $meta_key, $meta_value, $unique = false);

    public function read($id, $key = '', $single = false);

    public function update($id, $meta_key, $meta_value, $prev_value = '');

    public function delete($id, $meta_key, $meta_value = '');

    public function readByMid($mid);

    public function updateByMid($mid, $meta_key, $meta_value);

    public function deleteByMid($mid);
}
