<?php
/**
 * KWF Class: StepSorter, sets a "sort" attribute on table rows step by step by ordering up-/downwards
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 1.0
 */

class StepSorter
  {
  private $model;

  /*
   * Constructor: step_sorter
   *
   * @param object $model The model object which will be used to fetch sortable items
   * @return void
   */
  public function __construct($model)
    {
    $this->model = $model;
    }

  /*
   * Move item up (decrease sort number)
   *
   * @param object $item The item to move up, as an db_object
   * @param array(string) $args An array of strings with extra arguments to identify correct items
   * @return bool True on success, false on failure
   */
  public function up($item, $args = array())
    {
    /* Fetch first item that has a lower, or equal, sort number than this item */
    $previous_item = $this->model->fetchByOrder($item->order, '<=', 'DESC', $args);

    /* Did we find any item? */
    if ($previous_item)
      {
      /* Are the sort numbers equal? */
      if ($item->order == $previous_item->order)
        {
        /* Yes, let previous item have a higher sort number than this item */
        $previous_item->setOrder($item->order + 1);
        }
      else
        {
        /* No, swap sort numbers with these items */
        $temp = $item->order;
        $item->setOrder($previous_item->order);
        $previous_item->setOrder($temp);
        }

      /* Save the new sort numbers for other item */
      $previous_item->save();
      }
    else
      {
      /* Assume that this is the first item in order */
      $item->setOrder(1);
      }

    /* Save the new sort numbers for this item */
    $item->save();

    return true;
    }

  /*
   * Move item down (increase sort number)
   *
   * @param object $item The item to move down, as an db_object
   * @param array(string) $args An array of strings with extra arguments to identify correct items
   * @return bool True on success, false on failure
   */
  public function down($item, $args = array())
    {
    /* Fetch first item that has a higher sort number than this item */
    $next_item = $this->model->fetchByOrder($item->order, '>=', 'ASC', $args);

    /* Did we find any item? */
    if ($next_item)
      {
      /* Are the sort numbers equal? */
      if ($item->order == $next_item->order)
        {
        /* Yes, let next item have a lower sort number than this item */
        $next_item->setOrder($item->order - 1);
        }
      else
        {
        /* No, swap sort numbers with these items */
        $temp = $item->order;
        $item->setOrder($next_item->order);
        $next_item->setOrder($temp);
        }

      /* Save the new sort numbers for other item */
      $next_item->save();
      }
    else
      {
      /* Assume that this is the first item in order */
      $item->setOrder(1);
      }

    /* Save the new sort numbers for this item */
    $item->save();

    return true;
    }

  /*
   * Append item at bottom (set to highest sort number + 1) without saving
   *
   * @param object $item The item to append, as an db_object
   * @param array(string) $args An array of strings with extra arguments to identify correct items
   * @return bool True on success, false on failure
   */
  public function append($item, $args = array())
    {
    /* Fetch last item (item with highest sort number) */
    $last_item = $this->model->fetchByOrder(0, '>', 'DESC', $args);

    /* Did we find any item? */
    if ($last_item)
      {
      /* Yes, set the new items sort number to last items number + 1 */
      return $item->setOrder($last_item->order + 1);
      }
    else
      {
      /* No, assume that this is the first item in order */
      return $item->setOrder(1);
      }
    }
  }
?>