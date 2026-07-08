<?php

namespace App\Support;

use Illuminate\Http\Request;

class LandingSeo
{
    public static function make(Request $request): array
    {
        $lang = $request->query('lang', 'bn');
        $baseUrl = config('app.url');

        $data = [
            'bn' => [
                'title' => 'হিসাবিজ প্রপার্টি — রিয়েল এস্টেট ম্যানেজমেন্ট সফটওয়্যার | বাংলাদেশের #১ প্রপার্টি সলিউশন',
                'description' => 'হিসাবিজ প্রপার্টি দিয়ে আপনার রিয়েল এস্টেট ব্যবসা পরিচালনা করুন। প্রজেক্ট, বিল্ডিং, ফ্লোর, ইউনিট, বিনিয়োগকারী, গ্রাহক সব এক জায়গায়। ফ্রি ট্রায়াল শুরু করুন!',
                'keywords' => 'রিয়েল এস্টেট সফটওয়্যার, প্রপার্টি ম্যানেজমেন্ট, বাংলাদেশ, হিসাবিজ, ফ্ল্যাট বিক্রয়, বিল্ডিং ম্যানেজমেন্ট, ইউনিট ট্র্যাকিং',
            ],
            'en' => [
                'title' => 'Hishabiz Property — Real Estate Management Software | #1 Property Solution in Bangladesh',
                'description' => 'Manage your real estate business with Hishabiz Property. Projects, buildings, floors, units, investors, customers — all in one place. Start your free trial!',
                'keywords' => 'real estate software, property management, Bangladesh, Hishabiz, flat sale, building management, unit tracking',
            ],
        ];

        $current = $data[$lang] ?? $data['bn'];

        return [
            'htmlLocale' => $lang === 'bn' ? 'bn-BD' : 'en',
            'lang' => $lang,
            'brand' => $lang === 'bn' ? 'হিসাবিজ প্রপার্টি' : 'Hishabiz Property',
            'title' => $current['title'],
            'description' => $current['description'],
            'keywords' => $current['keywords'],
            'canonical' => $baseUrl . ($lang !== 'bn' ? '?lang=en' : ''),
            'image' => asset('assets/img/project/screenshot.webp'),
            'alternates' => [
                'bn' => $baseUrl,
                'en' => $baseUrl . '?lang=en',
                'x-default' => $baseUrl,
            ],
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => 'Hishabiz Property',
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'offers' => [
                    ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'BDT', 'name' => 'Free'],
                    ['@type' => 'Offer', 'price' => '999', 'priceCurrency' => 'BDT', 'name' => 'Pro'],
                    ['@type' => 'Offer', 'price' => '2999', 'priceCurrency' => 'BDT', 'name' => 'Enterprise'],
                ],
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => '4.8',
                    'reviewCount' => '320',
                ],
            ],
        ];
    }
}
