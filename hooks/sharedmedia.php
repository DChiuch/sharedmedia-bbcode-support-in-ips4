//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

class hook33 extends _HOOK_CLASS_
{

    public function bbcodeTags(\IPS\Member $member, $area)
    {
        $return = parent::bbcodeTags($member, $area);

        $return['sharedmedia'] = array(
            'tag' => 'span',
            'single' => TRUE,
            'allowOption' => TRUE,
            'callback' => function (\DOMElement $node, $matches, \DOMDocument $document) {
                try {
                    $parts = explode(":", $matches[2]);
                    switch ($parts[0]) {
                        case 'calendar':
                            try {
                                $event = \IPS\Db::i()->select('*', 'calendar_events', array('event_id=?', (int)$parts[2]))->first();
                                $url = \IPS\Http\Url::internal("app=calendar&controller=event&id={$event['event_id']}", 'front', 'calendar_event', $event['event_title_seo']);
                                //$content = "<p><a href='{$url}'>{$url}</a></p>";
                                $content = \IPS\Text\Parser::embeddableMedia( $url );
                            } catch (\Exception $e) {
                                $content = "";
                            }
                            break;

                        case 'gallery':
                            if (\IPS\Application::appIsEnabled('gallery') === FALSE) {
                                $content = "";
                            }

                            if ($parts[1] == 'images') {
                                try {
                                    $image = \IPS\gallery\Image::constructFromData(\IPS\Db::i()->select('*', 'gallery_images', array('image_id=?', (int)$parts[2]))->first());
                                    $content = "<p><a href='{$image->url()}'><img src='{$image->embedImage()->url}' alt='{$image->caption}'></a></p>";
                                } catch (\Exception $e) {
                                    $content = "";
                                }
                            } else {
                                try {
                                    $album = \IPS\gallery\Album::constructFromData(\IPS\Db::i()->select('*', 'gallery_albums', array('album_id=?', (int)$parts[2]))->first());
                                    $content = \IPS\Text\Parser::embeddableMedia( \IPS\Http\Url::createFromString( $album->url(), TRUE, TRUE ) );
                                } catch (\Exception $e) {
                                    $content = "";
                                }
                            }
                            break;

                        case 'downloads':
                            try {
                                $file = \IPS\Db::i()->select('*', 'downloads_files', array('file_id=?', (int)$parts[2]))->first();
                                $url = \IPS\Http\Url::internal("app=downloads&module=downloads&controller=view&id={$file['file_id']}", 'front', 'downloads_file', $file['file_name_furl']);
                                //$content = "<p><a href='{$url}'>{$url}</a></p>";
                                $content = \IPS\Text\Parser::embeddableMedia( $url );
                            } catch (\Exception $e) {
                                $content = "";
                            }
                            break;

                        case 'core':
                            $content = "[attachment={$parts[2]}:string]";
                            break;

                        case 'blog':
                            try {
                                $entry = \IPS\Db::i()->select('*', 'blog_entries', array('entry_id=?', (int)$parts[2]))->first();
                                $url = \IPS\Http\Url::internal("app=blog&module=blogs&controller=entry&id={$entry['entry_id']}", 'front', 'blog_entry', $entry['entry_name_seo']);
                                //$content = "<p><a href='{$url}'>{$url}</a></p>";
                                $content = \IPS\Text\Parser::embeddableMedia( $url );
                            } catch (\Exception $e) {
                                $content = "";
                            }
                            break;

                    }
                } catch (\Exception $e) {
                }

                $tmpDoc = new \DOMDocument();
                $tmpDoc->loadHTML($content);
                foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $childNode) {
                    $childNode = $document->importNode($childNode, true);
                    $node->appendChild($childNode);
                }

                return $node;
            }
        );

        return $return;
    }

}
