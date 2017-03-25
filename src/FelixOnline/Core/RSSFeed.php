<?php
namespace FelixOnline\Core;

class RSSFeed {
    private $channel_url;
    private $channel_title;
    private $channel_description;
    private $channel_lang;
    private $channel_copyright;
    private $channel_date;
    private $channel_creator;
    private $channel_subject;

    private $image_url;

    private $items = array();
    private $nritems;

    public function __construct() {
        $this->nritems=0;
        $this->channel_url='';
        $this->channel_title='';
        $this->channel_description='';
        $this->channel_lang='';
        $this->channel_copyright='';
        $this->channel_date='';
        $this->channel_creator='';
        $this->channel_subject='';
        $this->image_url='';
    }

    public function setChannel($url, $title, $description, $lang, $copyright, $creator) {
        $this->channel_url=$url;
        $this->channel_title=$title;
        $this->channel_description=$description;
        $this->channel_lang=$lang;
        $this->channel_copyright=$copyright;
        $this->channel_date=date("r");
        $this->channel_creator=$creator;

        return $this;
    }

    public function setImage($url) {
        $this->image_url=$url;

        return $this;
    }

    public function addItem($url, $title, $description,$pubDate) {
        $this->items[$this->nritems]['url']=$url;
        $this->items[$this->nritems]['title']=$title;
        $this->items[$this->nritems]['pubDate']=$pubDate;
        $this->items[$this->nritems]['description']=$description;
        $this->nritems++;

        return $this;
    }

    public function output() {
        $output =  '<?xml version="1.0" encoding="utf-8"?>'."\n";
        $output .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
        $output .= '<channel>'."\n";
        $output .= '<title>'.$this->channel_title.'</title>'."\n";
        $output .= '<atom:link href="'.$this->channel_url.'" rel="self" type="application/rss+xml" />'."\n";
        $output .= '<link>'.$this->channel_url.'</link>'."\n";
        $output .= '<description>'.$this->channel_description.'</description>'."\n";
        $output .= '<language>'.$this->channel_lang.'</language>'."\n";
        $output .= '<copyright>'.$this->channel_copyright.'</copyright>'."\n";
        $output .= '<pubDate>'.$this->channel_date.'</pubDate>'."\n";
        $output .= '<managingEditor>'.$this->channel_creator.'</managingEditor>'."\n";

        $output .= '<image>'."\n";
        $output .= '<url>'.$this->image_url.'</url>'."\n";
        $output .= '<title>'.$this->channel_title.'</title>'."\n";
        $output .= '<link>'.$this->channel_url.'</link>'."\n";
        $output .= '</image>'."\n";

        for($k=0; $k<$this->nritems; $k++) {
            $output .= '<item>'."\n";
            $output .= '<title>'.$this->items[$k]['title'].'</title>'."\n";
            $output .= '<link>'.$this->items[$k]['url'].'</link>'."\n";
            $output .= '<description>'.$this->items[$k]['description'].'</description>'."\n";
            $output .= '<guid>'.$this->items[$k]['url'].'</guid>'."\n";
            $output .= '<pubDate>'.$this->items[$k]['pubDate'].'</pubDate>'."\n";
            $output .= '</item>'."\n";
        };

        $output .= '</channel>'."\n";
        $output .= '</rss>'."\n";
        return $output;
    }
}
