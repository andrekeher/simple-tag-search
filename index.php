<?php 
/**
 * Plugin name: Simple Tag Search
 * Description: Use the post tags in a text search.
 * Plugin URI: https://github.com/andrekeher/tag-search.git
 * Version: 1.0.0
 * Author: andrekeher
 * Author URI: http://twitter.com/andrekeher
 */
class SimpleTagSearch
{
    protected function __construct()
    {
        add_filter('posts_join', array($this, 'join'), 10, 2);
        add_filter('posts_where', array($this, 'where'), 10, 2);
        add_filter('posts_groupby', array($this, 'groupBy'), 10, 2);
    }

    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance)
        {
            $instance = new static();
        }
        return $instance;
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }

    public function join($join, $query)
    {
        global $wpdb;
        if ($this->validateSearch($query))
        {
            $join .= sprintf(' LEFT JOIN (%s INNER JOIN %s ON %s.term_taxonomy_id = %s.term_taxonomy_id INNER JOIN %s ON %s.term_id = %s.term_id) ON %s.ID = %s.object_id ',
                $wpdb->term_relationships, $wpdb->term_taxonomy, $wpdb->term_taxonomy, $wpdb->
                term_relationships, $wpdb->terms, $wpdb->terms, $wpdb->term_taxonomy, $wpdb->
                posts, $wpdb->term_relationships);
        }
        return $join;
    }

    public function where($where, $query)
    {
        global $wpdb;
        if ($this->validateSearch($query))
        {
            $where .= sprintf(' OR (%s.taxonomy = \'post_tag\' AND %s.name LIKE \'%s%%\') ',
                $wpdb->term_taxonomy, $wpdb->terms, get_query_var('s'));
        }
        return $where;
    }

    public function groupBy($groupBy, $query)
    {
        global $wpdb;
        if ($this->validateSearch($query))
        {
            $groupBy = sprintf(' %s.ID ', $wpdb->posts);
        }
        return $groupBy;
    }

    private function validateSearch($query)
    {
        if ($query instanceof WP_Query && $query->is_main_query() && $query->is_search() &&
            !$query->is_admin())
        {
            return true;
        }
        return false;
    }
}
$objSimpleTagSearch = SimpleTagSearch::getInstance();