<?php

/**
 * Plugin Name:         BuddyPress Global Search BPDocs
 * Plugin URI:          https://github.com/rpi-virtuell/buddypress_global_search_bpdocs
 * Description:         Supports BuddyPress Docs in BuddyPress Global Search
 * Author:              Frank Neumann-Staude
 * Author URI:          https://staude.net
 * License:             GNU General Public License v2
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.html
 * Version:             1.0.0
 * GitHub Plugin URI:   https://github.com/rpi-virtuell/buddypress_global_search_bpdocs
 * GitHub Branch:       master
 * Requires WP:         4.0
 * Requires PHP:        5.3
 */


function rpi_BBoss_Global_Search_CPT_sql( $sql, $params  ) {
	if ( $params[ 'post_type' ] == 'bp_doc' ) {
		$a = new BP_Docs_Access_Query();

		$only_totalrow_count = $params[ 'only_totalrow_count' ];
		$search_term = $params[ 'search_term' ];

		global $wpdb;
		$query_placeholder = array();

		$sql = " SELECT ";

		if( $only_totalrow_count ){
			$sql .= " COUNT( DISTINCT id ) ";
		} else {
			$sql .= " DISTINCT id , %s as type, post_title LIKE '%%%s%%' AS relevance, post_date as entry_date  ";
			$query_placeholder[] = "cpt-bp_doc";
			$query_placeholder[] = $search_term;
		}

		$sql .= " FROM {$wpdb->prefix}posts p";

		$sql .= " WHERE 1=1 AND ( p.post_title LIKE %s OR p.post_content LIKE %s ";
		$query_placeholder[] = '%'.$search_term.'%';
		$query_placeholder[] = '%'.$search_term.'%';

		//Post should be publish
		$sql .= " ) AND p.post_type = %s AND p.post_status = 'publish' and p.ID not in (";
		$query_placeholder[] = "bp_doc";
		$sql .= implode( ',', $a->get_doc_ids() );
		$sql .= ')';

		$sql = $wpdb->prepare( $sql, $query_placeholder );

	}
	return $sql;
}


add_filter( 'BBoss_Global_Search_CPT_sql', 'rpi_BBoss_Global_Search_CPT_sql',  10, 2 );
