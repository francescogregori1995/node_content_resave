<?php

namespace Drupal\node_content_resave;

use Drupal\node\Entity\Node;

class NodeContentResaveBatch
{

  public static function getBatch($content_type, $update_changed)
  {
    $connection = \Drupal::database();
    $query = $connection->select('node_field_data', 'n')
      ->fields('n', ['nid'])
      ->condition('n.type', $content_type)
      ->execute()
      ->fetchAll();

    $nodes = [];
    foreach ($query as $row) {
      $nodes[] = $row->nid;
    }

    $operations = [];
    foreach ($nodes as $node) {
      $operations[] = ['\Drupal\node_content_resave\NodeContentResaveBatch::resaveNode', [$node, $update_changed]];
    }

    return [
      'title' => t('Resaving nodes...'),
      'operations' => $operations,
      'finished' => '\Drupal\node_content_resave\NodeContentResaveBatch::finishedCallback',
    ];
  }

  public static function resaveNode($nid, $update_changed, &$context)
  {
    $node = Node::load($nid);
    if ($update_changed !== '1') {
      // Recupera la vecchia data di modifica
      $old_changed_time = $node->getChangedTime();
      // Imposta la data di modifica con la vecchia data
      $node->setChangedTime($old_changed_time);
    }
    // salvo il nodo
    $node->save();
    // aggiorno il contesto
    $context['message'] = t('Resaved node @nid with title @title.', ['@nid' => $nid, '@title' => $current_time]);
    // aggiorno i risultati
    $context['results'][] = $nid;
  }

  public static function finishedCallback($success, $results, $operations)
  {
    $messenger = \Drupal::messenger();
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One node processed.',
        '@count nodes processed.'
      );
      $messenger->addMessage($message);
    } else {
      $message = t('Finished with an error.');
      $messenger->addError($message);
    }
  }
}
