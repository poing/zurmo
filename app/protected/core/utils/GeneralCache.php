<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * This is a general cache helper that utilizes both php caching and memcaching if available. Utilized for
     * caching requirements that are simple in/out of a serialized array or string of information.
     */
    class GeneralCache
    {
        private static $cachedEntries = array();

        protected static $cacheIncrementValueVariableName = 'CacheIncrementValue';

        public static $additionalStringForCachePrefix = '';

        public static function getEntry($identifier)
        {
            assert('is_string($identifier)');
            if (PHP_CACHING_ON)
            {
                if (isset(self::$cachedEntries[$identifier]))
                {
                    return self::$cachedEntries[$identifier];
                }
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($identifier);

                @$serializedData = Yii::app()->cache->get($prefix . $identifier);
                if ($serializedData !== false)
                {
                    $unserializedData = unserialize($serializedData);
                    if (PHP_CACHING_ON)
                    {
                        self::$cachedEntries[$identifier] = $unserializedData;
                    }
                    return $unserializedData;
                }
            }
            throw new NotFoundException();
        }

        public static function cacheEntry($identifier, $entry)
        {
            assert('is_string($identifier)');
            assert('is_string($entry) || is_array($entry) || is_numeric($entry) || is_object($entry) || $entry == null');
            if (PHP_CACHING_ON)
            {
                self::$cachedEntries[$identifier] = $entry;
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($identifier);
                @Yii::app()->cache->set($prefix . $identifier, serialize($entry));
            }
        }

        public static function forgetEntry($identifier)
        {
            if (PHP_CACHING_ON)
            {
                if (isset(self::$cachedEntries[$identifier]))
                {
                    unset(self::$cachedEntries[$identifier]);
                }
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($identifier);
                @Yii::app()->cache->delete($prefix . $identifier);
            }
        }

        public static function forgetAll()
        {
            if (PHP_CACHING_ON)
            {
                self::$cachedEntries = array();
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                self::incrementCacheIncrementValue();
            }
        }

        protected static function getCachePrefix($identifier)
        {
            if (self::isIdentifierCacheIncrementValueName($identifier))
            {
                $prefix = ZURMO_TOKEN . '_' . "G:";
            }
            else
            {
                $cacheIncrementValue = self::getCacheIncrementValue();
                $prefix = ZURMO_TOKEN . '_' . $cacheIncrementValue . '_' . "G:";
            }

            if (self::getAdditionalStringForCachePrefix() != '')
            {
                $prefix = self::getAdditionalStringForCachePrefix() . '_' . $prefix;
            }

            return $prefix;
        }

        protected static function getCacheIncrementValue()
        {
            try
            {
                $cacheIncrementValue = self::getEntry(self::$cacheIncrementValueVariableName);
            }
            catch (NotFoundException $e)
            {
                $cacheIncrementValue = 0;
                self::setCacheIncrementValue($cacheIncrementValue);
            }
            return $cacheIncrementValue;
        }

        protected static function setCacheIncrementValue($value)
        {
            self::cacheEntry(self::$cacheIncrementValueVariableName, $value);
        }

        protected static function incrementCacheIncrementValue()
        {
            $currentCacheIncrementValue = self::getCacheIncrementValue();
            $currentCacheIncrementValue++;
            self::setCacheIncrementValue($currentCacheIncrementValue);
        }

        protected static function isIdentifierCacheIncrementValueName($identifier)
        {
            if ($identifier == self::$cacheIncrementValueVariableName)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function setAdditionalStringForCachePrefix($prefix = '')
        {
            self::$additionalStringForCachePrefix = $prefix;
        }

        public static function getAdditionalStringForCachePrefix()
        {
            return self::$additionalStringForCachePrefix;
        }
    }
?>
