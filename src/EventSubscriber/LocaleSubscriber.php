<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\AcceptHeader;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;
    private array $supportedLocales;

    public function __construct(string $defaultLocale = 'en', array $supportedLocales = ['en', 'fr'])
    {
        $this->defaultLocale = $defaultLocale;
        $this->supportedLocales = $supportedLocales;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Si la locale est déjà définie (par exemple via l'URL), ne la change pas
        if ($request->attributes->get('_locale')) {
            return;
        }

        $preferredLangs = AcceptHeader::fromString($request->headers->get('Accept-Language'))->all();

        foreach ($preferredLangs as $lang => $header) {
            $locale = strtolower(substr($lang, 0, 2));
            if (in_array($locale, $this->supportedLocales, true)) {
                $request->setLocale($locale);
                return;
            }
        }

        // Fallback par défaut
        $request->setLocale($this->defaultLocale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
