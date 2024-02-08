<?php

declare(strict_types=1);

namespace App\Livewire;

use Exception;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Livewire\Component;

class Browser extends Component
{
    public string $content = '';

    public string $url = 'https://www.google.com';

    public function handleLoad(): void
    {
        if (empty($this->url)) {
            $this->content = "URL is empty";
            return;
        }

        $browser = (new BrowserFactory())->createBrowser();

        try {
            // creates a new page and navigate to an URL
            $page = $browser->createPage();
            $navigation = $page->navigate($this->url);
            $navigation->waitForNavigation(Page::DOM_CONTENT_LOADED);

            // get page title
            // $title = $page->evaluate('document.title')->getReturnValue();

            // get page body
            $body = $page->evaluate('document.documentElement.innerHTML')->getReturnValue();

            $this->content = $body ?? 'No content';
        } catch (Exception $e) {
            $this->content = $e->getMessage();
        } finally {
            $browser->close();
        }
    }

    public function render()
    {
        return view('livewire.browser');
    }
}
