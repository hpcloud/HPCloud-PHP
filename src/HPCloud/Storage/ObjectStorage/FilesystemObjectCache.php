<?php
/* ============================================================================
(c) Copyright 2012 Hewlett-Packard Development Company, L.P.
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights to
use, copy, modify, merge,publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE  LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
============================================================================ */
/**
 * @file
 *
 * Contains the filesystem implementaiton of an ObjectCache interface.
 */

namespace HPCloud\Storage\ObjectStorage;

class FielsystemObjectCache implements ObjectCache {
  const BASEDIR_KEY = 'hpcloud.swift.cache.basedir';
  public function __construct() {
    $tmp = sys_get_temp_dir();
    $this->basedir = \HPCloud\Bootstrap::config(BASEDIR_KEY, $tmp);
  }

  public function has($obj) {
    return file_exists($this->pathTo($obj));
  }

  public function get($obj, $res) {
    $handle = fopen('r', $this->pathTo($obj));
    $res->setFile($handle);
    return $res;
  }

  public function put($obj, $res) {
    $handle = fopen('w', $this->pathTo($obj));
    stream_copy_to_stream($res->file(), $handle);
    // fclose($res->file());

    $res->setFile($handle, true);
    return $res;
  }

  /**
   * Get the path to an object.
   */
  protected function pathTo($obj) {
    $parts = array(
      $this->basedir,
      md5($obj->url()),
      $obj->eTag(),
    );
    return implode(DIRECTORY_SEPARATOR, $parts);
  }
}
