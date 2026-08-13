<?php

require_once('./core/Icon.php');

class MenuEntry implements JsonSerializable
{
    private string $title;
    private string $href;
    private Icon $icon;

    public function __construct(string $title, string $href, Icon $icon)
    {
        $this->title = $title;
        $this->href = $href;
        $this->icon = $icon;
    }

    public static function Get(string $title, string $href, Icon $icon)
    {
        return new self(
            title: $title,
            href: $href,
            icon: $icon,
        );
    }

    public function jsonSerialize(): mixed
    {
        return array(
            'title' => $this->title,
            'href' => $this->href,
            'icon' => $this->icon
        );
    }
}

class MenuEntryArrayFactory{
    public static function GetMenuForShow($name, $id)
    {
        return MenuEntryArray::Empty()
            ->Add(
                MenuEntry::Get(
                    title: 'Edit',
                    href: 'internal:'.$name.'.Edit?id='.$id,
                    icon: Icon::EDIT
                )
            )
            ->Add(
                MenuEntry::Get(
                    title: 'Duplicate',
                    href: 'internal:'.$name.'.Duplicate?id='.$id,
                    icon: Icon::CONTENT_COPY
                )
            )
            ->Add(
                MenuEntry::Get(
                    title: 'Report',
                    href: 'https://dev.tittlus.com/framework/server/?actionKey=Task.Report&target=_blank&id='.$id,
                    icon: Icon::PICTURE_AS_PDF
                )
            )
            ->Add(
                MenuEntry::Get(
                    title: 'Delete',
                    href: 'internal:'.$name.'.DeleteAndShowGrid?must-confirm=true&id='.$id,
                    icon: Icon::DELETE
                )
            );
    }

    public static function GetMenuForNew($name, $formId){
        return  MenuEntryArray::Empty()
            ->Add(
                MenuEntry::Get(
                    title: 'Save',
                    href: 'internal:'.$name.'.InsertAndShowGrid?form-id='.$formId,
                    icon: Icon::SAVE
                )
            );
    }

    public static function GetMenuForDuplicate($name, $formId){
        return  MenuEntryArray::Empty()
            ->Add(
                MenuEntry::Get(
                    title: 'Save',
                    href: 'internal:'.$name.'.InsertAndShowGrid?form-id='.$formId,
                    icon: Icon::SAVE
                )
            );
    }

    public static function GetMenuForEdit($name, $formId){
        return  MenuEntryArray::Empty()
            ->Add(
                MenuEntry::Get(
                    title: 'Save',
                    href: 'internal:'.$name.'.UpdateAndShowGrid?form-id='.$formId,
                    icon: Icon::SAVE
                )
            );
    }

    public static function GetMenuForEditGrid($name, $EditGridContainerId)
    {
        return MenuEntryArray::Empty()
            ->Add(
                MenuEntry::Get(
                    title: 'Add',
                    href: 'internal:'.$name.'.New',
                    icon: Icon::ADD
                )
            )
            ->Add(
                MenuEntry::Get(
                    title: 'Refresh',
                    href: 'internal:'.$name.'.EditGrid',
                    icon: Icon::REFRESH
                )
            )
            ->Add(
                MenuEntry::Get(
                    title: 'Filter',
                    href: 'internal:'.$name.'.FilterForm',
                    icon: Icon::FILTER_LIST
                )
            )
            ->Add(
                MenuEntry::Get(
                    title: 'Search',
                    href: 'javascript:SearchBar.Show(\''.$EditGridContainerId.'\');',
                    icon: Icon::SEARCH
                )
            );
    }
}

class MenuEntryArray extends ArrayObject implements JsonSerializable
{
    public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator")
    {
        foreach ($input as $item) {
            if (!$item instanceof MenuEntry) {
                throw new InvalidArgumentException('Value must be a MenuEntry!');
            }
        }
        parent::__construct($input, $flags, $iterator_class);
    }

    public function offsetSet($key, $val): void
    {
        if ($val instanceof MenuEntry) {
            parent::offsetSet($key, $val);
            return;
        }
        throw new InvalidArgumentException('Value must be a MenuEntry!');
    }

    public static function Empty()
    {
        return new self();
    }

    public function Add(MenuEntry $MenuEntry)
    {
        $this[] = $MenuEntry;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getArrayCopy();
    }
}

class Form implements JsonSerializable
{
    private string $title;
    private string $href;
    private MenuEntryArray $menu;
    private string $template;

    private string $target;

    public function __construct(string $title, string $href, MenuEntryArray $menu, string $template, string $target)
    {
        $this->title = $title;
        $this->href = $href;
        $this->menu = $menu;
        $this->template = $template;
        $this->target = $target;
    }

    public static function Get(?string $title = null, ?string $href = null, ?MenuEntryArray $menu = null, ?string $template = null, string $target = 'main')
    {
        $title = $title ?? '';
        $href = $href ?? '';
        $menu = $menu ?? MenuEntryArray::Empty();
        $template = $template ?? '';

        return new self(
            title: $title,
            href: $href,
            menu: $menu,
            template: $template,
            target: $target
        );
    }

    public function jsonSerialize(): mixed
    {
        return array(
            'title' => $this->title,
            'href' => $this->href,
            'menu' => $this->menu,
            'target' => $this->target,
            'template' => $this->template,
        );
    }
}
