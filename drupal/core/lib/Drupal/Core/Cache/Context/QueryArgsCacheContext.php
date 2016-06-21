<?php

namespace Drupal\Core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * Defines the QueryArgsCacheContext service, for "per query args" caching.
 *
 * Cache context ID: 'url.query_args' (to vary by all query arguments).
 * Calculated cache context ID: 'url.query_args:%key', e.g.'url.query_args:foo'
 * (to vary by the 'foo' query argument).
 */
class QueryArgsCacheContext extends RequestStackCacheContextBase implements CalculatedCacheContextInterface {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Query arguments');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($query_arg = NULL) {
    if ($query_arg === NULL) {
      return $this->requestStack->getCurrentRequest()->getQueryString();
    }
    elseif ($this->requestStack->getCurrentRequest()->query->has($query_arg)) {
      $value = $this->requestStack->getCurrentRequest()->query->get($query_arg);
      if ($value !== '') {
        return $value;
      }
      else {
        return '?valueless?';
      }
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($query_arg = NULL) {
    return new CacheableMetadata();
  }

}
