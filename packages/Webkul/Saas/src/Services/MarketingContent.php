<?php

namespace Webkul\Saas\Services;

class MarketingContent
{
    /**
     * Create a new content resolver.
     */
    public function __construct(protected PlatformSettings $settings) {}

    /**
     * The configured platform display name.
     */
    public function platformName(): string
    {
        return $this->settings->platformName();
    }

    /**
     * Hero copy, falling back to the translated defaults.
     *
     * @return array{title: string, description: string, primary: string, secondary: string}
     */
    public function hero(): array
    {
        return [
            'title' => $this->text('marketing_hero_title', trans('saas::marketing.home.hero.title')),
            'description' => $this->text('marketing_hero_description', trans('saas::marketing.home.hero.description')),
            'primary' => $this->text('marketing_hero_primary_label', trans('saas::marketing.home.hero.primary')),
            'secondary' => $this->text('marketing_hero_secondary_label', trans('saas::marketing.home.hero.secondary')),
        ];
    }

    /**
     * A validated brand accent colour.
     */
    public function brandColor(): string
    {
        $colour = (string) $this->settings->get('marketing_brand_color', config('saas.marketing.defaults.brand_color'));

        return preg_match('/^#[0-9a-f]{6}$/i', $colour) === 1 ? $colour : '#4338ca';
    }

    /**
     * The announcement banner shown across the public website.
     */
    public function banner(): ?string
    {
        $banner = trim((string) $this->settings->get('marketing_banner', ''));

        return $banner === '' ? null : $banner;
    }

    /**
     * The public contact email address, when one is configured.
     */
    public function contactEmail(): ?string
    {
        $email = trim((string) $this->settings->get('marketing_contact_email', ''));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        $support = trim($this->settings->supportEmail());

        return $support !== '' && filter_var($support, FILTER_VALIDATE_EMAIL) ? $support : null;
    }

    /**
     * The public contact phone number, when one is configured.
     */
    public function contactPhone(): ?string
    {
        $phone = trim((string) $this->settings->get('marketing_contact_phone', ''));

        return $phone === '' ? null : mb_substr($phone, 0, 40);
    }

    /**
     * Configured social profiles keyed by network.
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        $stored = $this->settings->get('marketing_social_links', []);

        if (! is_array($stored)) {
            return [];
        }

        $links = [];

        foreach ((array) config('saas.marketing.social', []) as $network) {
            $url = trim((string) ($stored[$network] ?? ''));

            if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            if (! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                continue;
            }

            $links[$network] = $url;
        }

        return $links;
    }

    /**
     * Whether public tenant registration is currently open.
     */
    public function registrationEnabled(): bool
    {
        return $this->settings->registrationEnabled();
    }

    /**
     * An owner-authored About introduction, when one is configured.
     */
    public function aboutIntro(): ?string
    {
        $intro = trim((string) $this->settings->get('marketing_about_intro', ''));

        return $intro === '' ? null : $intro;
    }

    /**
     * Addresses notified about new contact enquiries.
     *
     * @return string[]
     */
    public function contactRecipients(): array
    {
        $configured = trim((string) $this->settings->get('contact_notifications_email', ''));

        $candidates = $configured !== ''
            ? preg_split('/[,;]+/', $configured) ?: []
            : [$this->settings->supportEmail()];

        $recipients = [];

        foreach ($candidates as $candidate) {
            $email = trim((string) $candidate);

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = $email;
            }
        }

        return array_values(array_unique($recipients));
    }

    /**
     * Read a non-empty stored string or the translated default.
     */
    protected function text(string $key, string $default): string
    {
        $value = trim((string) $this->settings->get($key, ''));

        return $value === '' ? $default : $value;
    }
}
