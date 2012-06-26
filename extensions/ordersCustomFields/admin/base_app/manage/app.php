<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/*
	Orders Custom Fields Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

$ExtOrdersCustomFields = $appExtension->getExtension('ordersCustomFields');

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));

$csv = '"Bay Lake Tower at Disney\'s Contemporary Resort","4600 North World Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Contemporary Resort","4600 North World Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Grand Floridian Resort & Spa","4401 Floridian Way","Lake Buena Vista","FL",32830,(407) 934-7639
"The Villas at Disney\'s Wilderness Lodge","901 Timberline Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Wilderness Lodge","901 Timberline Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Polynesian Resort","1600 Seven Seas Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Walt Disney World Swan and Dolphin Resort","1500 Epcot Resorts Boulevard","Lake Buena Vista","FL",32830,(407) 934-4000
"Villas of Grand Cypress","One North Jacaranda","Orlando","FL",32836,(407) 239-4700
"Disney\'s Port Orleans Resort - Riverside","1251 Riverside Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Beach Club Villas","1800 Epcot Resorts Boulevard","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Port Orleans Resort - French Quarter","2201 Orleans Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Yacht and Beach Club Resort","1700 Epcot Resorts Boulevard","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s BoardWalk Villas","2101 Epcot Resorts Boulevard","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Coronado Springs Resort","1000 West Buena Vista Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Old Key West Resort","1510 North Cove Road","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s BoardWalk Inn","2101 Epcot Resorts Boulevard","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Caribbean Beach Resort","900 Cayman Way","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Saratoga Springs Resort & Spa","1960 Broadway","Lake Buena Vista","FL",32830,(407) 934-7639
"Buena Vista Palace Hotel and Spa","1900 East Buena Vista Drive","Lake Buena Vista","FL",32830,(407) 827-2727
"Disney\'s Animal Kingdom Lodge","2901 Osceola Parkway","Lake Buena Vista","FL",32830,(407) 934-7639
"Wyndham Bonnet Creek Resort","9560 Via Encinas","Lake Buena Vista","FL",32830,(407) 238-3500
"Hyatt Regency Grand Cypress","One Grand Cypress Boulevard","Orlando","FL",32836,(407) 239-1234
"Staybridge Suites Orlando/Lake Buena Vista","8751 Suiteside Drive","Orlando","FL",32836,(407) 238-0777
"Disney\'s All-Star Sports Resort","1701 West Buena Vista Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Fairfield Inn & Suites Lake Buena Vista","12191 South Apopka-Vineland Road","Lake Buena Vista","FL",32836,(800) 693-5401
"Arnold Palmer\'s Bay Hill Club & Lodge","9000 Bay Hill Boulevard","Orlando","FL",32819,(407) 876-2429
"Sheraton Lake Buena Vista Resort","12205 South Apopka Vineland Road","Orlando","FL",32836,(407) 239-0444
"Orlando Vista Hotel, an Ascend Collection Hotel","12490 Apopka Vineland Road","Orlando","FL",32836,(407) 239-4646
"Best Western Lake Buena Vista Resort Hotel in the WDW Resort","2000 Hotel Plaza Boulevard","Lake Buena Vista","FL",32830,(407) 828-2424
"Grande Villas Resort","8651 Treasure Cay Lane","Orlando","FL",32836,(407) 238-2300
"Wyndham Lake Buena Vista Resort","1850 Hotel Plaza Boulevard","Lake Buena Vista","FL",32830,(407) 828-4444
"Disney\'s Pop Century Resort","1050 Century Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Hilton Orlando Bonnet Creek","14100 Bonnet Creek Resort Lane","Orlando","FL",32821,(407) 597-3600
"Westgate Blue Tree at Lake Buena Vista","12007 Cypress Run Road","Orlando","FL",32836,(407) 597-2200
"Wyndham Grand Orlando Resort Bonnet Creek","14651 Chelonia Parkway","Orlando","FL",32821,(407) 390-2300
"BlueTree Resort at Lake Buena Vista","12007 Cypress Run Road","Orlando","FL",32836,(407) 238-6000
"Courtyard by Marriott - Lake Buena Vista at Vista Centre","8501 Palm Parkway","Lake Buena Vista","FL",32836,(407) 239-6900
"DoubleTree Suites by Hilton Lake Buena Vista","2305 Hotel Plaza Boulevard","Lake Buena Vista","FL",32830,(407) 934-1000
"Hilton Orlando Resort, Lake Buena Vista","1751 Hotel Plaza Boulevard","Lake Buena Vista","FL",32830,(407) 827-4000
"Disney\'s All-Star Music Resort","1801 West Buena Vista Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Hawthorn Suites by Wyndham Lake Buena Vista","8303 Palm Parkway","Orlando","FL",32836,(407) 597-5000
"Holiday Inn - In the Walt Disney World® Resort","1805 Hotel Plaza Boulevard","Lake Buena Vista","FL",32830,(407) 828-8888
"Waldorf Astoria Orlando","14200 Bonnet Creek Resort Lane","Orlando","FL",32821,(407) 597-5500
"Extended Stay Deluxe Lake Buena Vista","8100 Palm Parkway","Orlando","FL",32836,(407) 239-4300
"Radisson Hotel Orlando - Lake Buena Vista","12799 Apopka Vineland Road","Orlando","FL",32836,(407) 597-3400
"Clarion Inn Lake Buena Vista","8442 Palm Parkway","Lake Buena Vista","FL",32836,(407) 996-7300
"Disney\'s All-Star Movies Resort","1901 West Buena Vista Drive","Lake Buena Vista","FL",32830,(407) 934-7639
"Parc Soleil by Hilton Grand Vacations Club","11272 Desforges Avenue","Orlando","FL",32836,(407) 465-4000
"Courtyard Orlando Lake Buena Vista in the Marriott Village","8623 Vineland Avenue","Orlando","FL",32821,(407) 938-9001
"Springhill Suites Orlando Lake Buena Vista in the Marriott Village","8623 Vineland Avenue","Orlando","FL",32821,(407) 938-9001
"Embassy Suites Hotel Lake Buena Vista","8100 Lake Street","Orlando","FL",32836,(407) 239-1144
"Hilton Garden Inn Lake Buena Vista - Orlando","11400 Marbella Palm Court","Orlando","FL",32836,(407) 239-9550
"Fairfield Inn & Suites Orlando Lake Buena Vista in the Marriott Village","8615 Vineland Avenue","Orlando","FL",32821,(407) 938-9001
"Holiday Inn Club Vacations At Orange Lake Resort","8505 West Irlo Bronson Memorial Highway","Kissimmee","FL",34747,(407) 239-0000
"Palisades Resort","14200 Avalon Road","Orlando","FL",34787,(321) 250-3030
"Lighthouse Key Resort & Spa","8545 West Irlo Bronson Memorial Highway","Kissimmee","FL",34747,(407) 997-0733
"Residence Inn by Marriott - Lake Buena Vista","11450 Marbella Palms Court","Orlando","FL",32836,(407) 465-0075
"Sheraton Vistana Resort Villas","8800 Vistana Centre Drive","Orlando","FL",32821,(407) 239-3100
"Westgate Towers","7600 West Irlo Bronson Memorial Highway","Kissimmee","FL",34743,(407) 396-2500
"Holiday Inn Resort - Lake Buena Vista","13351 State Road 535","Orlando","FL",32821,(407) 239-4500
"Worldgate Resort","3011 Maingate Lane","Kissimmee","FL",34747,(407) 396-1400
"Blue Heron Beach Resort","13428 Blue Heron Beach Drive","Lake Buena Vista","FL",32821,(407) 387-2200
"Orlando World Center Marriott","8701 World Center Drive","Orlando","FL",32821,(407) 239-4200
"Nickelodeon Suites Resort","14500 Continental Gateway","Orlando","FL",32821,(407) 387-5437
"Hilton Grand Vacations Club on International Drive","8122 Arrezzo Way","Orlando","FL",32821,(407) 465-2600
"The Palms Hotel & Villas","3100 Parkway Boulevard","Kissimmee","FL",34747,(407) 396-2229
"Westgate Town Center","4000 Westgate Blvd","Kissimmee","FL",34747,(407) 239-0510
"Westgate Vacation Villas","4000 Westgate Blvd,","Kissimmee","FL",34747,(407) 239-0510
"Residence Inn Orlando at SeaWorld®","11000 Westwood Boulevard","Orlando","FL",32821,(407) 313-3600
"The Fountains - A Bluegreen Resort","12400 South International Drive","Orlando","FL",32821,(407) 905-4100
"Westgate Lakes Resort & Spa","10000 Turkey Lake Road","Orlando","FL",32819,(407) 345-0000
"Lake Eve Resort","12388 International Drive South","Orlando","FL",32821,(407) 597-0370
"Quality Suites Universal","9350 Turkey Lake Road","Orlando","FL",32819,(407) 351-5050
"Floridays Resort Orlando","12562 International Drive","Orlando","FL",32821,(866) 994-6321
"Grand Beach","8317 Lake Bryan Beach Boulevard","Orlando","FL",32821,(407) 238-2500
"WorldQuest Resort","8849 WorldQuest Boulevard","Orlando","FL",32821,(407) 387-3800
"Rosen Inn at Pointe Orlando","9000 International Drive","Orlando","FL",32819,(407) 996-8585
"Buena Vista Suites","8203 World Center Drive","Orlando","FL",32821,(407) 239-8588
"Rosen Plaza Hotel","9700 International Drive","Orlando","FL",32819,(407) 996-9700
"Sheraton Vistana Villages Resort Villas","12401 International Drive","Orlando","FL",32821,(407) 238-5000
"Drury Inn and Suites Orlando ","7271 West Sand Lake Road","Orlando","FL",32819,(800) 378-7946
"EconoLodge Inn & Suites","8738 International Drive","Orlando","FL",32819,(407) 345-8195
"Embassy Suites Hotel Orlando-I Drive/Convention Center","8978 International Drive","Orlando","FL",32819,(407) 352-1400
"Caribe Royale All-Suite Hotel & Convention Center","8101 World Center Drive","Orlando","FL",32821,(407) 238-8000
"Westgate Leisure Resort","6950 Villa de Costa Drive","Orlando","FL",32821,(407) 239-8855
"Courtyard by Marriott International Drive/Convention Center","8600 Austrian Court","Orlando","FL",32819,(407) 351-2244
"Hilton Grand Vacations Club at SeaWorld","6924 Grand Vacations Way","Orlando","FL",32821,(407) 239-0100
"La Quinta Inn at International Drive","8300 Jamaican Court","Orlando","FL",32819,(407) 351-1660
"Radisson Hotel Orlando International Drive","8444 International Drive","Orlando","FL",32819,(407) 345-0505
"Ramada Convention Center I-Drive","8342 Jamaican Court","Orlando","FL",32819,(407) 363-1944
"Embassy Suites Hotel Orlando International Drive/Jamaican Court","8250 Jamaican Court","Orlando","FL",32819,(407) 345-8250
"Peabody Orlando","9801 International Drive","Orlando","FL",32819,(407) 352-4000
"Renaissance Orlando At SeaWorld","6677 Sea Harbor Drive","Orlando","FL",32821,(407) 351-5555
"Best Western International Drive - Orlando","8222 Jamaican Court","Orlando","FL",32819,(407) 345-1172
"Hampton Inn Orlando International Drive/Convention Center","8900 Universal Boulevard","Orlando","FL",32819,(407) 354-4447
"Homewood Suites By Hilton - International Drive near Universal Studios","8745 International Drive","Orlando","FL",32819,(407) 248-2232
"Hyatt Place Orlando Convention Center/International Drive","8741 International Drive","Orlando","FL",32819,(407) 370-4720
"Monumental Hotel Orlando","12120 International Drive","Orlando","FL",32821,(407) 239-1222
"Staybridge Suites - Orlando/International Drive","8480 International Drive","Orlando","FL",32819,(407) 352-2400
"The Holiday Inn Resort Orlando - The Castle","8629 International Drive","Orlando","FL",32819,(407) 345-1511
"Caribe Cove Resort","9000 Treasure Trove Lane","Kissimmee","FL",34747,(407) 997-4444
"Residence Inn by Marriott/Convention Center","8800 Universal Boulevard","Orlando","FL",32819,(800) 551-1276
"Springhill Suites by Marriott Orlando Convention Center/International Drive Area","8840 Universal Boulevard","Orlando","FL",32819,(800) 693-5407
"Extended Stay Deluxe Convention Center - Pointe Orlando","8750 Universal Boulevard","Orlando","FL",32819,(407) 903-1500
"Extended Stay Deluxe Convention Center - Westwood Boulevard","6443 Westwood Boulevard","Orlando","FL",32821,(407) 351-1982
"Hawthorn Suites Orlando Convention Center","6435 Westwood Boulevard","Orlando","FL",32821,(407) 351-6600
"Hilton Garden Inn Orlando at SeaWorld","6850 Westwood Boulevard","Orlando","FL",32821,(407) 354-1500
"La Quinta Inn & Suites Convention Center","8504 Universal Boulevard","Orlando","FL",32819,(407) 345-1365
"Summer Bay Resort","25 Town Center Boulevard, Suite C","Clermont","FL",34714,(352) 242-1100
"CoCo Key Hotel and Water Resort Orlando","7400 International Drive","Orlando","FL",32819,(407) 351-2626
"Quality Inn International","7600 International Drive","Orlando","FL",32819,(407) 996-1600
"Best Western Plus Orlando Convention Center Hotel","6301 Westwood Boulevard","Orlando","FL",32821,(407) 313-4100
"Celebration Suites","5820 West Irlo Bronson Memorial Highway","Kissimmee","FL",34746,(407) 396-7900
"Holiday Inn Main Gate East","5711 West Irlo Bronson Memorial Highway","Kissimmee","FL",34746,(407) 396-4222
"Rosen Centre Hotel","9840 International Drive","Orlando","FL",32819,(407) 996-9840
"Wyndham Orlando Resort","8001 International Drive","Orlando","FL",32819,(407) 351-2420
"Clarion Inn & Suites I-Drive/Convention Center","9956 Hawaiian Court","Orlando","FL",32819,(407) 351-5100
"Lexington Suites Orlando near Universal","7400 Canada Avenue","Orlando","FL",32819,(407) 363-0332
"Red Roof Inn #200","9922 Hawaiian Court","Orlando","FL",32819,(407) 352-1507
"Studio 6 Extended Stay","5733 West Irlo Bronson Memorial Highway","Kissimmee","FL",34746,(407) 390-1869
"Hawthorn Suites by Wyndham Universal Orlando","7601 Canada Avenue","Orlando","FL",32819,(407) 581-2151
"Ramada Plaza Resort & Suites International Drive Orlando","6500 International Drive","Orlando","FL",32819,(407) 345-5340
"Days Inn Convention Center/International Drive","9990 International Drive","Orlando","FL",32819,(407) 352-8700
"International Palms Resort","6515 International Drive","Orlando","FL",32819,(407) 351-3500
"Parc Corniche Condominium Suite Hotel","6300 Parc Corniche Drive","Orlando","FL",32821,(407) 239-7100
"Quality Inn & Suites Orlando","7495 Canada Avenue","Orlando","FL",32819,(407) 351-7000
"Residence Inn by Marriott - International Drive","7975 Canada Avenue","Orlando","FL",32819,(407) 345-0117
"Crowne Plaza Orlando Universal","7800 Universal Boulevard","Orlando","FL",32819,(407) 355-0550
"DoubleTree by Hilton Orlando at SeaWorld","10100 International Drive","Orlando","FL",32821,(407) 352-1100
"Enclave Suites","6165 Carrier Drive","Orlando","FL",32819,(407) 351-1155
"Lake Buena Vista Resort Village & Spa","8113 Resort Village Drive","Orlando","FL",32821,(407) 597-0214
"Comfort Inn Universal Studios Area","6101 Sand Lake Road","Orlando","FL",32819,(407) 363-7886
"Rosen Inn closest to Universal","6327 International Drive","Orlando","FL",32819,(407) 996-4444
"The Westin Imagine Orlando","9501 Universal Boulevard","Orlando","FL",32819,(407) 233-2200
"Westgate Palace","6145 Carrier Drive","Orlando","FL",32819,(407) 966-6000
"Comfort Suites Maingate East","2775 Florida Plaza Boulevard","Kissimmee","FL",34746,(407) 397-7848
"Country Inn & Suites - by Carlson Orlando Universal Florida","7701 Universal Boulevard","Orlando","FL",32819,(407) 313-4200
"Hilton Orlando","6001 Destination Parkway","Orlando","FL",32819,(407) 313-4300
"Holiday Inn Express & Suites, Orlando/Lake Buena Vista East","3484 Polynesian Isle Boulevard","Kissimmee","FL",34746,(407) 997-1700
"Monumental Movieland Hotel","6233 International Drive","Orlando","FL",32819,(407) 351-3900
"Fairfield Inn & Suites Orlando at SeaWorld®","10815 International Drive","Orlando","FL",32821,(407) 354-1139
"Loews Royal Pacific Resort at Universal Orlando®","6300 Hollywood Way","Orlando","FL",32819,(407) 503-3000
"Point Orlando Resort","7389 Universal Boulevard","Orlando","FL",32819,(407) 956-2000
"Springhill Suites Orlando at SeaWorld","10801 International Drive","Orlando","FL",32821,(407) 354-1176
"Best Western Plus Orlando Gateway","7299 Universal Boulevard","Orlando","FL",32819,(407) 351-5009
"Four Points by Sheraton Orlando Studio City","5905 International Drive","Orlando","FL",32819,(407) 351-2100
"Hampton Inn South of Universal Studios","7110 South Kirkman Road","Orlando","FL",32819,(407) 345-1112
"Rodeway Inn Universal Studios Area","7050 South Kirkman Road","Orlando","FL",32819,(407) 351-2000
"Hilton Garden Inn Orlando International Drive North","5877 American Way","Orlando","FL",32819,(407) 363-9332
"Wyndham Cypress Palms","5324 Fairfield Lake Drive","Kissimmee","FL",34746,(800) 347-0149
"Country Inn & Suites Orlando - Maingate","5001 Calypso Cay Way","Kissimmee","FL",34746,(407) 997-1400
"Vista Cay Resort By Millenium","9924 Universal Blvd, Ste 244","Orlando","FL",32819,(407) 996-4647
"Bahama Bay Resort & Spa","400 Gran Bahama Boulevard","Davenport","FL",33897,(863) 547-1200
"Hard Rock Hotel® at Universal Orlando®","5800 Universal Boulevard, B110","Orlando","FL",32819,(407) 503-2000
"Hampton Inn & Suites Orlando/South Lake Buena Vista","4971 Calypso Cay Way","Kissimmee","FL",34746,(407) 396-8700
"Embassy Suites Orlando - Lake Buena Vista South","4955 Kyngs Heath Road","Kissimmee","FL",34746,(407) 597-4000
"Loews Portofino Bay Hotel at Universal Orlando®","5601 Universal Boulevard","Orlando","FL",32819,(407) 503-1000
"Legacy Vacation Resort Orlando","2800 North Poinciana Boulevard","Kissimmee","FL",34746,(407) 997-5000
"Disney\'s Animal Kingdom Villas - Jambo House","2901 Osceola Parkway","Lake Buena Vista","FL",32830,(407) 934-7639
"Disney\'s Animal Kingdom Villas - Kidani Village","3701 Osceola Parkway","Lake Buena Vista","FL",32830,(407) 934-7639
"Hyatt Place Orlando/Universal","5895 Caravan Court","Orlando","FL",32819,(407) 351-0627
"DoubleTree by Hilton at the Entrance to Universal Orlando","5780 Major Boulevard","Orlando","FL",32819,(407) 351-1000
"Days Inn Orlando - Maingate To Universal","5827 Caravan Court","Orlando","FL",32819,(407) 351-3800
"Fairfield Inn & Suites by Marriott near Universal Orlando Resort","5614 Vineland Road","Orlando","FL",32819,(407) 581-5600
"Rosen Shingle Creek","9939 Universal Boulevard","Orlando","FL",32819,(407) 996-9939
"JW Marriott Orlando, Grande Lakes","4040 Central Florida Parkway","Orlando","FL",32837,(407) 206-2300
"The Ritz-Carlton Orlando, Grande Lakes","4012 Central Florida Parkway","Orlando","FL",32837,(407) 206-2400
"Hampton Inn Florida Mall","8601 South Orange Blossom Trail","Orlando","FL",32809,(407) 859-4100
"Omni Orlando Resort at ChampionsGate","1500 Masters Boulevard","ChampionsGate","FL",33896,(407) 390-6664
"The Florida Hotel and Conference Center","1500 Sand Lake Road","Orlando","FL",32809,(407) 859-1500
"Travelodge Inn & Suites Orlando Airport","1853 McCoy Road","Orlando","FL",32809,(407) 851-1113
"Comfort Suites Orlando Airport","1936 McCoy Road","Orlando","FL",32809,(407) 812-9100
"Parliament House","410 N. Orange Blossom Trail","Orlando","FL",32805,(407) 425-7571
"The Grand Bohemian Hotel","325 South Orange Avenue","Orlando","FL",32801,(407) 313-9000
"Sheraton Orlando Downtown Hotel","400 West Livingston Street","Orlando","FL",32801,(407) 843-6664
"Embassy Suites Orlando - Downtown","191 East Pine Street","Orlando","FL",32801,(407) 841-1000
"Courtyard by Marriott Orlando Downtown","730 North Magnolia Avenue","Orlando","FL",32803,(407) 996-1000
"DoubleTree by Hilton Orlando Downtown","60 South Ivanhoe Boulevard","Orlando","FL",32804,(407) 425-4455
"Hyatt Regency Orlando International Airport","9300 Airport Boulevard, Main Terminal","Orlando","FL",32827,(407) 825-1234
"Hampton Inn & Suites Orlando Gateway/Orlando Airport","5460 Gateway Village Circle","Orlando","FL",32812,(407) 857-2830
"Comfort Suites Downtown Orlando","2416 North Orange Avenue","Orlando","FL",32804,(407) 228-4007
"Hyatt Place Orlando Airport Northwest","5435 Forbes Place","Orlando","FL",32812,(407) 816-7800
"Orlando Airport Courtyard","7155 North Frontage Road","Orlando","FL",32812,(407) 240-7200
"Renaissance Orlando Hotel - Airport","5445 Forbes Place","Orlando","FL",32812,(407) 513-7220
"The Orlando Airport Hotel","5555 Hazeltine National Drive","Orlando","FL",32812,(407) 856-0100
"Hilton Garden Inn Orlando Airport","7300 Augusta National Drive","Orlando","FL",32822,(407) 240-3725
"Holiday Inn Orlando International Airport","5750 T.G. Lee Boulevard","Orlando","FL",32822,(407) 851-6400
"Sheraton Suites Orlando Airport","7550 Augusta National Drive","Orlando","FL",32822,(407) 240-5555
"Staybridge Suites Orlando International Airport","7450 Augusta National Drive","Orlando","FL",32822,(407) 438-2121
"Orlando Airport Marriott","7499 Augusta National Drive","Orlando","FL",32822,(407) 851-9000
"Embassy Suites Orlando Airport","5835 T.G. Lee Boulevard","Orlando","FL",32822,(407) 888-9339
"Springhill Suites by Marriott Orlando Airport","5828 Hazeltine National Drive","Orlando","FL",32822,(407) 816-5533
"Radisson Hotel Orlando - UCF","724 North Alafaya Trail","Orlando","FL",32826,(407) 658-9008
"Liki Tiki Village","17777 Bali Boulevard","Kissimmee","FL",34787,(407) 856-7190
"Reunion Resort and Club, Wyndham Grand Resort","7593 Gathering Drive","Reunion","FL",34747,(407) 662-1100
"Royal Plaza Hotel","1905 Hotel Plaza Boulevard","Orlando","FL",32830,(407) 828-2828
"Travelodge Suites Maingate","4694 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-1780
"Travelodge Suites East Gate Orange","5399 W. Irlo Bronson Memorial Hwy. 
","Kissimmee","FL",34746,(407) 396-7666
"Super 8 Motel Maingate","5875 W. Irlo Bronson Memorial Hwy. ","Kissimmee","FL",34746,(407) 396-8883
"Super 8 Kissimmee Suites","1815 W. Vine St.","Kissimmee","FL",34741,(407) 847-6121
"Stadium Inn & Suites","2039 E. Irlo Bronson Memorial Hwy. ","Kissimmee","FL",34744,(407) 846-7814
"Seralago Hotel & Suites Main Gate East","5678 W. Irlo Bronson Memorial Hwy. ","Kissimmee","FL",34746,(407) 396-4488
"Runaway Beach Club","3000 Bonfire Beach Dr. ","Kissimmee","FL",34746,(407) 997-3656
"Royal Celebration Inn","4944 West Irlo Bronson Mem. Hwy. ","Kissimmee","FL",34746,(407) 396-4455
"Rodeway Inn MainGate","5995 W. Irlo Bronson Memorial Hwy. ","Kissimmee","FL",34747,(407) 396-4300
"Rodeway Inn Eastgate","4559 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-1212
"Ramada Maingate West","7491 W. Irlo Bronson Memorial Hwy. ","Kissimmee","FL",34747,(407) 396-6000
"Ramada Gateway Hotel","7470 W. Irlo Bronson Memorial Hwy. ","Kissimmee","FL",34747,(407) 396-4400
"Radisson Orlando Celebration Resort","2900 Parkway Blvd.","Kissimmee","FL",34747,(407) 396-7000
"Quality Suites Royale Parc Suites","5876 W. Irlo Bronson Memorial Hwy. ","Kissimmee","FL",34746,(407) 396-8040
"Quality Inn & Suites Maingate","2945 Entry Point Blvd. ","Kissimmee","FL",34747,(407) 390-9780
"Quality Inn & Suites Eastgate","4960 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-1376
"Parkway International Resort","6200 Safari Trl.","Kissimmee","FL",34746,(407) 856-7190
"Orlando Courtyard Suites","2950 Reedy Creek Blvd.","Kissimmee","FL",34747,(407) 465-0234
"Orbit One Vacation Villas","2950 Entry Point Blvd.","Kissimmee","FL",34747,(407) 856-7190
"Mystic Dunes Resort & Golf Club","7900 Mystic Dunes Lane ","Kissimmee","FL",34747,(407) 396-1311
"Meliá Orlando Suite Hotel at Celebration","225 Celebration Place","Kissimmee","FL",34747,(407) 964-7000
"Maingate Lakeside Resort","7769 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34747,(407) 396-2222
"Maingate Inn","5840 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-7969
"Magic Tree Resort","2795 N. Old Lake Wilson Road ","Kissimmee","FL",34747,(407) 396-2300
"Magic Castle Inn & Suites","5055 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-2212
"Knights Inn Maingate","7475 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34747,(407) 396-4200
"Kissimmee Howard Johnson Tropical Palms","4311 W. Vine St.","Kissimmee","FL",34746,(407) 396-7100
"Inn at Oak Plantation (The)","4125 W. Vine St.","Kissimmee","FL",34741,(407) 944-5600
"Howard Johnson Express Inn & Suites Lakefront Park","4836 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-4762
"Golden Link Motel","4914 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-0555
"Golden Crystal Inn","1620 W. Vine St.","Kissimmee","FL",34741,(407) 343-1333
"GoldStar Inn & Suites","4600 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 390-1330
"Gaylord Palms Resort and Convention Center","6000 Osceola Pkwy.","Kissimmee","FL",34746,(407) 586-2000
"Gator Motel","4576 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-0127
"Galleria Palms Hotel","3000 Maingate Lane","Kissimmee","FL",34747,(407) 396-6300
"Friendly Village Inn Motel","2550 E. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34744,(407) 846-1007
"Econolodge Hotel & Suites","2934 Polynesian Isles Boulevard","Kissimmee","FL",34746,(407) 787-4100
"Econo Lodge Maingate Central","4669 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-1890
"Destiny Palms Hotel Maingate West","8536 W. Hwy. 192 ","Kissimmee","FL",34747,(407) 396-1600
"Comfort Inn Maingate Kissimmee","7675 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34747,(407) 396-4000
"Club Sevilla","4646 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-1800
"Clarion Suites Maingate","7888 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34747,(407) 390-9888
"Clarion Resort Waterpark & Conference Center","2261 E. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34744,(407) 846-2221
"Claremont Kissimmee Hotel","6051 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34747,(407) 396-1748
"Chateau Motel","3518 W. Vine St.","Kissimmee","FL",34744,(407) 847-3477
"Champions World Resort","8660 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34747,(407) 396-4500
"Calypso Cay Suites","4991 Calypso Cay Way","Kissimmee","FL",34746,(407) 997-1300
"Budget Inn West","4686 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-2322
"Bohemian Hotel Celebration","700 Bloom St. ","Kissimmee","FL",34747,(407) 566-6000
"Baymont Inn & Suites","4156 W. Vine St.","Kissimmee","FL",34741,(407) 994-1900
"Barefoot’n Resort","2750 Florida Plaza Blvd.","Kissimmee","FL",34746,(407) 856-7190
"America’s Best Inn Main Gate East","5150 W. Irlo Bronson Memorial Hwy.","Kissimmee","FL",34746,(407) 396-1111';
/*
foreach(explode("\n", $csv) as $i => $line){
	$cols = explode(',', $line);

	$NewOption = new OrdersCustomFieldsOptions();
	$NewOption->extra_data = json_encode(array(
		'room_number' => '',
		'gate_code' => '',
		'street_address' => str_replace('"', '', $cols[1]),
		'street_address_2' => '',
		'city' => str_replace('"', '', $cols[2]),
		'state' => str_replace('"', '', $cols[3]),
		'postcode' => str_replace('"', '', $cols[4]),
		'telephone' => str_replace('"', '', $cols[5])
	));

	$NewOption->Description[1]->option_name = str_replace('"', '', $cols[0]);
	$NewOption->Description[1]->language_id = 1;

	$OptionToField = new OrdersCustomFieldsOptionsToFields();
	$OptionToField->field_id = 1;
	$OptionToField->display_order = $i;

	$NewOption->Fields->add($OptionToField);
	$NewOption->save();
}
*/