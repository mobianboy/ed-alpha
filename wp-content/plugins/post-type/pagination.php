<?php


/**
 * @desc pagination controller class
 * @author SDK (steve@eardish.com)
 * @date 2012-11-03
 */
class pagination {


  /**
   * @desc Pagination vars 
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
  */
  protected $limit; // How many items per page?
  protected $total; // How many total items?
  protected $page; // What page are we on?
  protected $last_page; // What is the last page in the set?
  protected $next_page; // What is the next page in the set?
  protected $first_item; // What is the first item number on the current page?
  protected $last_item; // What is the last item number on the current page?


  /**
   * @desc Build pagination properties
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @param int $page - The current page number (default 1)
   * @param int $limit - The max number of items per page (default 15)
   * @param int $total - The total number of item results in this archive instance
   * @return none
  */
  protected function __construct($page=1, $limit=15, $total) {
    $this->limit = $limit;
    $this->total = $total;
    $this->set_page($page);
    $this->set_last_page();
    $this->set_next_page();
    $this->set_first_item();
    $this->set_last_item();
  } // end function __construct


  /**
   * @desc Set the current page being viewed
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @param int [OPTIONAL] $page - The current page number (default 1)
   * @return none
  */
  protected function set_page($page=1) {
    $this->page = ($page < 1) ? 1 : $page;
  } // end function set_page


  /**
   * @desc Set the last page of the set
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return none
  */
  protected function set_last_page() {
  	$last_page = ceil($this->total / $this->limit);
  	if($last_page < 1) {
      $last_page = 1;
    }
  	$this->last_page = $last_page;
  } // end function set_last_page


  /**
   * @desc Set the next page
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return none
  */
  protected function set_next_page() {
    $next_page = $this->page + 1;
    if($next_page > $this->last_page) {
    	$next_page = $this->last_page;
    }
    $this->next_page = $next_page;
  } // end function set_next_page


  /**
   * @desc Set the first item number on the current page (in relation to the total items in full unpaginated set) 
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return none
  */
  protected function set_first_item() {
    $this->first_item = ($this->page - 1) * $this->limit;
  } // end function set_first_item


  /**
   * @desc Set the last item number on the current page (in relation to the total items in full unpaginated set) 
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return none
  */
  protected function set_last_item() {
    $this->last_item = ($this->page * $this->limit) - 1;
    if($this->last_item > ($this->total - 1)) {
      $this->last_item = $this->total - 1;
    }
  } // end function set_last_item


  /**
   * @desc Get the limit per page
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return int - Page item count limit
  */
  protected function get_limit() {
    return $this->limit;
  } // end function get_limit


  /**
   * @desc Get the total count of items
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return int - The total count of items
  */
  protected function get_total() {
    return $this->total;
  } // end function get_total


  /**
   * @desc Get the current page
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return int - Page number
  */
  protected function get_page() {
    return $this->page;
  } // end function get_page


  /**
   * @desc Get the last page in the set
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return int - The last page number of the set
  */
  protected function get_last_page() {
    return $this->last_page;
  } // end function get_last_page


  /**
   * @desc Get the next page in the set 
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return int - The next page number of the set
  */
  protected function get_next_page() {
    return $this->next_page;
  } // end function get_next_page


  /**
   * @desc Get the first item number on the current page
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return int - The number of the first item on the current page (in relation to the full unpaginated set) 
  */
  protected function get_first_item() {
    return $this->first_item;
  } // end function get_first_item


  /**
   * @desc Get the last item number on the current page
   * @author SDK (steve@eardish.com)
   * @date 2012-11-03
   * @return int - The number of the last item on the current page (in relation to the full unpaginated set) 
  */
  protected function get_last_item() {
    return $this->last_item;
  } // end function get_last_item


} // end class pagination

