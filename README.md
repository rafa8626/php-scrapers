# Scrapers

Collection of scraper scripts written in PHP that grab the decisions from public legal websites, showing different levels of complexity to obtain them.

## Resources
1. PHP 7.1 (enhance speed and type hinting)
2. PHP DOM library (query the HTML structure accurately)
3. Regular Expressions (obtain name and ID of each case)

## List of scrapers

### North Carolina - Supreme Court
https://appellate.nccourts.org/opinion-filings/?c=sc.

Method used: `GET`.

Grabs the decisions from the current year.

After checking the structure of the website form to parse the archive, same URL is being used,
with the only difference that a new query string to search by year is appended, giving the search
range between 1998 and the current year.

### New York - Court of Appeals
http://iapps.courts.state.ny.us/lawReporting/Search?searchType=opinion

Method used: `POST`.

The URL shows a form to grab decisions from different NY courts. So, tampering the information using the Firefox add-on 
[Tamper Data for FF Quantum](https://github.com/Pamblam/Tamper-Data-for-FF-Quantum), the system submits a POST call to update the list of cases.

The scripts tries to search from 1998 to the current year using a start and end dates, up to the current date today.
All the documents grabbed are in HTML format


## How to execute?

Run `php index.php` at the root of this project,  and you will be presented with a menu of options.
Choose one of them, or exit just hit Enter.

## TODO

1. Include SQL database schema.
3. Add classes and commands to save results in database using PDO.
