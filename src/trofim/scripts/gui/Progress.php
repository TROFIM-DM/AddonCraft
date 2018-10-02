<?php
namespace trofim\scripts\gui;

use gui;

class Progress
{
    
    private $progress;
    
    function __construct (UXNode $object, string $text = 'Загрузка...')
    {
        $preloader = new UXProgressIndicator();
        $preloader->progressK = -1;
        $preloader->size = [42, 42];
        $preloader->style = '-fx-effect: dropshadow(gaussian, rgba(0, 0, 0, 0.4), 3, 0.4, 0, 0);';
        
        $label = new UXLabelEx($text);
        $label->wrapText = true;
        
        $box = new UXVBox([$preloader, $label]);
        $box->style = '-fx-background-color: rgba(128, 128, 128, 0.7);';
        $box->alignment = 'CENTER';
        $box->position = $object->position;
        $box->size = $object->size;
        $box->classes->add('classProgress');
        
        $object->parent->add($box);
        
        $this->progress = $box;
        
        return $this;
    }
    
    function setText ($text)
    {
        $this->progress->children[1]->text = $text;
    }
    
    function free ()
    {
        $this->progress->free();
    }
    
}