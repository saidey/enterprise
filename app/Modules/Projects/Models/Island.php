<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Island extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;
    use SoftDeletes;

    public static array $atollNames = [
        'HA' => 'Haa Alif',
        'HDh' => 'Haa Dhaalu',
        'Sh' => 'Shaviyani',
        'N' => 'Noonu',
        'R' => 'Raa',
        'B' => 'Baa',
        'Lh' => 'Lhaviyani',
        'K' => 'Kaafu',
        'AA' => 'Alif Alif',
        'ADh' => 'Alif Dhaalu',
        'V' => 'Vaavu',
        'M' => 'Meemu',
        'F' => 'Faafu',
        'Dh' => 'Dhaalu',
        'Th' => 'Thaa',
        'L' => 'Laamu',
        'GA' => 'Gaafu Alif',
        'GDh' => 'Gaafu Dhaalu',
        'Gn' => 'Gnaviyani',
        'S' => 'Seenu',
    ];

    public static array $defaultLocations = [
        'AA' => ['Bodufolhudhoo','Feridhoo','Himandhoo','Maalhos','Mathiveri','Rasdhoo','Thoddoo','Ukulhas'],
        'ADh' => ['Dhangethi','Dhigurah','Dhidhdhoo','Fenfushi','Hangnaameedhoo','Kunburudhoo','Maamigili','Mahibadhoo','Mandhoo','Omadhoo'],
        'B' => ['Dharavandhoo','Dhonfanu','Eydhafushi','Fehendhoo','Fulhadhoo','Goidhoo','Hithaadhoo','Kamadhoo','Kendhoo','Kihaadhoo','Kudarikilu','Maalhos','Thulhaadhoo'],
        'Dh' => ['Bandidhoo','Hulhudheli','Kudahuvadhoo','Maaenboodhoo','Meedhoo','Rinbudhoo'],
        'F' => ['Bileiydhoo','Dharanboodhoo','Feeali','Magoodhoo','Nilandhoo'],
        'GA' => ['Dhaandhoo','Dhevvadhoo','Gemanafushi','Kanduhulhudhoo','Kolamaafushi','Kondey','Maamendhoo','Nilandhoo','Villingili'],
        'GDh' => ['Faresmaathodaa','Fiyoari','Gahdhoo','Hoadehdhoo','Madaveli','Nadellaa','Rathafandhoo','Thinadhoo','Vaadhoo'],
        'Gn' => ['Fuvahmulah'],
        'HA' => ['Baarah','Dhidhdhoo','Filladhoo','Hoarafushi','Ihavandhoo','Kelaa','Maarandhoo','Mulhadhoo','Muraidhoo','Thakandhoo','Thuraakunu','Uligamu','Utheemu','Vashafaru'],
        'HDh' => ['Finey','Hanimaadhoo','Hirimaradhoo','Kulhudhuffushi','Kumundhoo','Kurinbi','Makunudhoo','Naivaadhoo','Nellaidhoo','Neykurendhoo','Nolhivaramu','Nolhivaranfaru','Vaikaradhoo'],
        'K' => ['Dhiffushi','Gaafaru','Gulhi','Guraidhoo','Himmafushi','Huraa','Kaashidhoo','Maafushi','Thulusdhoo'],
        'L' => ['Dhanbidhoo','Fonadhoo','Gan','Hithadhoo','Isdhoo','Kalaidhoo','Kunahandhoo','Maabaidhoo','Maamendhoo','Maavah','Mundoo'],
        'Lh' => ['Hinnavaru','Kurendhoo','Naifaru','Olhuvelifushi'],
        'M' => ['Dhiggaru','Kolhufushi','Maduvvari','Mulah','Muli','Naalaafushi','Raiymandhoo','Veyvah'],
        'N' => ['Fohdhoo','Henbadhoo','Holhudhoo','Kendhikulhudhoo','Kudafari','Landhoo','Lhohi','Maafaru','Maalhendhoo','Magoodhoo','Manadhoo','Miladhoo','Velidhoo'],
        'R' => ['Alifushi','Angolhitheemu','Dhuvaafaru','Fainu','Hulhudhuffaaru','Inguraidhoo','Innamaadhoo','Kinolhas','Maakurathu','Maduvvari','Meedhoo','Rasgetheemu','Rasmaadhoo','Ungoofaaru','Vaadhoo'],
        'S' => ['Feydhoo','Hithadhoo','Hulhudhoo','Maradhoo','Maradhoo-Feydhoo','Meedhoo'],
        'Sh' => ['Bileffahi','Feevah','Feydhoo','Foakaidhoo','Funadhoo','Goidhoo','Kanditheemu','Komandoo','Lhaimagu','Maaungoodhoo','Maroshi','Milandhoo','Narudhoo','Noomaraa'],
        'Th' => ['Burunee','Dhiyamigili','Gaadhiffushi','Guraidhoo','Hirilandhoo','Kandoodhoo','Kinbidhoo','Madifushi','Omadhoo','Thimarafushi','Vandhoo','Veymandoo','Vilufushi'],
        'V' => ['Felidhoo','Fulidhoo','Keyodhoo','Rakeedhoo','Thinadhoo'],
        '' => ['Malé','Hulhumalé','Vilimalé','Hulhulé'],
    ];

    protected $fillable = [
        'company_id',
        'location_type',
        'country',
        'region',
        'city',
        'name',
        'atoll',
        'manager_id',
        'notes',
        'kpis',
    ];

    protected $casts = [
        'kpis' => 'array',
    ];

    /**
     * Seed default Maldives locations for a given company.
     */
    public static function seedDefaultForCompany(string $companyId): void
    {
        foreach (self::$defaultLocations as $code => $islands) {
            $atollName = self::$atollNames[$code] ?? 'Malé City';
            foreach ($islands as $name) {
                self::withoutGlobalScopes()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'name' => $name,
                    ],
                    [
                        'country' => 'Maldives',
                        'region' => $atollName,
                        'location_type' => 'island',
                        'atoll' => $code,
                    ]
                );
            }
        }
    }
}
