# Sequence plugin to maintain ordered list of records

[![Build Status](https://img.shields.io/travis/ADmad/cakephp-sequence/master.svg?style=flat-square)](https://travis-ci.org/ADmad/cakephp-sequence)
[![Coverage](https://img.shields.io/coveralls/ADmad/cakephp-sequence/master.svg?style=flat-square)](https://coveralls.io/r/ADmad/cakephp-sequence)
[![Total Downloads](https://img.shields.io/packagist/dt/admad/cakephp-sequence.svg?style=flat-square)](https://packagist.org/packages/admad/cakephp-sequence)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.txt)

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require admad/cakephp-sequence
```

Then load the plugin by adding the following to your app's config/boostrap.php:

\Cake\Core\Plugin::load('ADmad/Sequence');

or using CakePHP's console:

./bin/cake plugin load ADmad/Sequence

## How it works

`SequenceBehavior` provided by this plugin maintains a contiguous sequence of 
integers in a selected column, for records in a table records (optionally with grouping) 
when adding, editing (including moving groups) or deleting records.

## Usage

Add the `SequenceBehavior` for your table and viola:

```php
$this->addBehavior('ADmad/Sequence');
```

You can customize various options as shown:

```php
$this->addBehavior('ADmad/Sequence', [
    'order' => 'position', // Field to use to store integer sequence. Default "position".
    'scope' => ['group_id'], // Array of field names to use for grouping records. Default [].
    'start' => 1, // Initial value for sequence. Default 1.
]);
```

## Acknowledgement

Shout out to @neilcrookes for his wonderful Sequence Behavior for CakePHP 1.3 which was the inspiration for this plugin.
