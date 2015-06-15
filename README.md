# talkalot-apps-api

## Requirements

These packages are **mandatory**.

* PHP 5.3 or newer
* PHP-CURL
* SOX
* SOX-MP3

If you don't have these packages installed, please google it depending on your operating system.

## Instalation

Clone this repository
```
$ cd ~/
$ git clone https://github.com/luismr/talkalot-apps-api.git
$ cd talkalot-apps-api
```

Then create a symbolic link in you **Asterisk AGI** directory pointing to your cloned copy, for example in Ubuntu do

```
$ cd /usr/share/asterisk/agi-bin
$ sudo ln -s ~/talkalot-apps-api/tts-engine/agi/say.php .
$ ls -l say.php
```
## Configuration

Please edit **say.php** and inform provided credentials as bellow

```php
#!/usr/bin/php -q
<?

/*
 * Configuration Settings
 */

$licence = "YORLICENCEHERE";
$key = "YOURLICENSEKEYHERE";

/*
 * Do not edit after this
 */

if (!defined('__ROOT__')) {

```

## Usage

LigFlat Talkalot TTS Engine API is very simple to be used as an Asterisk AGI script.

In your extensions.conf
```
exten 123,1,NoOp(LigFlat Talkalot TTS Engine Example)
exten 123,n,Wait(3)
exten 123,n,Answer()
exten 123,n,agi(say.php,<language>,<gender>,<text>,[forcePost])
exten 123,n,Wait(1)
exten 123,n,Hangup()
```

Where:

- language (**mandatory**) - language of voice to synthesize
   - **en-US** - English (United States)
   - **es-ES** - Spanish (Spain)
   - **pt-BR** - Portuguese (Brazil)
- gender (**mandatory**) - gender of voice to synthesize
   - male
   - female
- text (**mandatory**) - text to synthesize 
- forcePost (*optional*) - for large texts we force post request with right encoding handling
   - true
   - false

## Where to buy

Please go to our Website to more information or ask in our contact form at http://www.ligflat.com.br
