<?php
namespace TriTan\Interfaces;

interface PostMetaDataInterface
{
    public function create($post_id, $meta_key, $meta_value, $unique = false);

    public function read($post_id, $key = '', $single = false);

    public function update($post_id, $meta_key, $meta_value, $prev_value = '');

    public function delete($post_id, $meta_key, $meta_value = '');

    public function readByMid($mid);

    public function updateByMid($mid, $meta_key, $meta_value);

    public function deleteByMid($mid);
}
