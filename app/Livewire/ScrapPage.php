<?php

declare(strict_types=1);

namespace App\Livewire;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;
use Exception;
use HeadlessChromium\Browser;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Livewire\Component;

class ScrapPage extends Component
{
    public string $content = '';

    public array $data = [];

    public string $url = 'https://google.com';

    public function handleLoad(): void
    {
        if (empty($this->url)) {
            $this->content = "URL is empty";
            return;
        }

        $browser = (new BrowserFactory())->createBrowser();

        try {
            $linkStack = [$this->url];
            $processedLinks = [$this->url];

            // exit if execution time is more than 30 seconds
            $start = time();
            $maxExecutionTime = 100;
            $maxStackLength = 100;

            ini_set('max_execution_time', 300);
            set_time_limit(300);

            while (!empty($linkStack)) {
                if (time() - $start > $maxExecutionTime) {
                    // info('Execution time exceeded');
                    break;
                }

                $currentLink = array_shift($linkStack);

                // info('start: ' . $currentLink);

                [$content, $filteredLinks] = $this->handleScrap($browser, $currentLink);
                $this->dispatch('scraped');

                $this->data[] = [
                    'url' => $currentLink,
                    'content' => $content,
                ];

                $linkCount = count($linkStack);
                if ($linkCount < $maxStackLength) {
                    foreach ($filteredLinks as $link) {
                        if (!in_array($link, $processedLinks) && $linkCount < $maxStackLength) {
                            $linkStack[] = $link;
                            $processedLinks[] = $link;
                        }

                        $linkCount++;
                    }
                } else {
                    // info('Max stack length reached');
                }



                // info('done: ' . $currentLink);
            }


            // $this->content = $content ?? 'No content';
        } catch (Exception $e) {
            $this->content = $e->getMessage();
        } finally {
            $browser->close();
        }
    }

    public function render()
    {
        return view('livewire.scrap-page');
    }

    public function handleScrap(Browser $browser, string $url): array
    {
        // info('Scraping: ' . $url);

        // creates a new page and navigate to an URL
        $page = $browser->createPage();
        $navigation = $page->navigate($url);
        $navigation->waitForNavigation(Page::DOM_CONTENT_LOADED);

        // get page body
        $body = $page->evaluate('document.body.innerHTML')->getReturnValue();


        // Define the current URL
        $currentUrl = $this->url;
        $currentUrlParsed = parse_url($currentUrl);
        $currentUrlScheme = $currentUrlParsed["scheme"];
        $currentUrlHost = $currentUrlParsed["host"];

        // Remove "www." prefix from the host if present
        $currentUrlHost = Str::startsWith($currentUrlHost, "www.")
            ? Str::after($currentUrlHost, "www.")
            : $currentUrlHost;

        $doc = new DOMDocument();
        @$doc->loadHTML($body);
        $xpath = new DOMXPath($doc);
        $links = $xpath->evaluate("//a/@href");

        // Filtered links array
        $filteredLinks = [];

        foreach ($links as $link) {
            $link = $link->value;

            // Parse the link
            $parsedLink = parse_url($link);
            $linkScheme = $parsedLink["scheme"] ?? "";
            $linkHost = $parsedLink["host"] ?? "";
            $linkPath = $parsedLink["path"] ?? "";

            // Remove "www." prefix from the host if present
            $linkHost = Str::startsWith($linkHost, "www.")
                ? Str::after($linkHost, "www.")
                : $linkHost;

            // Handle absolute URLs with matching hosts
            if (
                ($linkScheme === "http" || $linkScheme === "https") &&
                $linkHost === $currentUrlHost
            ) {
                $filteredLink = $linkScheme . "://" . $linkHost . $linkPath;
                $filteredLink = rtrim($filteredLink, "/"); // Remove trailing slash
                if (!in_array($filteredLink, $filteredLinks)) {
                    $filteredLinks[] = $filteredLink;
                }
            }
            // Handle relative URLs (starts with '/')
            elseif (Str::startsWith($link, "/") && !Str::startsWith($link, "/#")) {
                $newLink = $currentUrlScheme . "://" . $currentUrlHost . $link;
                $newLink = rtrim($newLink, "/"); // Remove trailing slash
                if (!in_array($newLink, $filteredLinks)) {
                    $filteredLinks[] = $newLink;
                }
            }
        }

        $body = preg_replace('/<header\b[^>]*>(.*?)<\/header>/is', "", $body);
        $body = preg_replace('/<footer\b[^>]*>(.*?)<\/footer>/is', "", $body);

        $body = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $body);
        $body = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $body);

        $content = strip_tags($body);
        $content = preg_replace('/\s+/', ' ', $content);

        return [$content, $filteredLinks];
    }
}
