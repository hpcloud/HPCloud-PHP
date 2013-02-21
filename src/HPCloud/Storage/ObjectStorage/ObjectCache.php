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
 * Contains the ObjectCache interface.
 */

namespace HPCloud\Storage\ObjectStorage;

/**
 * A cache for storing object data.
 *
 * The contents of remote objects may be cached near-side to avoid re-fetching
 * the entire payload from object storage.
 *
 */
interface ObjectCache {
  /**
   * Checks whether the object is currently stored.
   * @param RemoteObject $obj
   */
  public function has($obj);
  /**
   * Get a cached version of the object.
   *
   * This returns a Response object with appropriate fields set.
   *
   * @param RemoteObject $obj
   * @param \HPCloud\Transport\Response
   * @return \HPCloud\Transport\Response
   */
  public function get($obj, $res);
  /**
   * Put the object's contents.
   *
   * This returns a Response object with appropriate fields set.
   *
   * @param RemoteObject $obj
   * @param \HPCloud\Transport\Response
   * @return \HPCloud\Transport\Response
   */
  public function put($obj, $res);
}

