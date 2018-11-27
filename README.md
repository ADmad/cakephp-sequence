# Sequence plugin to maintain ordered list of records

[![Build Status](https://img.shields.io/travis/ADmad/cakephp-sequence/master.svg?style=flat-square)](https://travis-ci.org/ADmad/cakephp-sequence)
[![Coverage](https://img.shields.io/codecov/c/github/ADmad/cakephp-sequence.svg?style=flat-square)](https://codecov.io/github/ADmad/cakephp-sequence)
[![Total Downloads](https://img.shields.io/packagist/dt/admad/cakephp-sequence.svg?style=flat-square)](https://packagist.org/packages/admad/cakephp-sequence)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.txt)

## Installation

Install this plugin into your CakePHP application using [composer](http://getcomposer.org):

```
composer require admad/cakephp-sequence
```

Then load the plugin by either running:

```bash
./bin/cake plugin load ADmad/Sequence
```

or adding the following line to `config/bootstrap.php`:

```php
\Cake\Core\Plugin::load('ADmad/Sequence');
```

## How it works

`SequenceBehavior` provided by this plugin maintains a contiguous sequence of
integers in a selected column, for records in a table records (optionally with grouping)
when adding, editing (including moving groups) or deleting records.

## Usage

Add the `SequenceBehavior` for your table and viola:

```php
$this->addBehavior('ADmad/Sequence.Sequence');
```

You can customize various options as shown:

```php
$this->addBehavior('ADmad/Sequence.Sequence', [
    'order' => 'position', // Field to use to store integer sequence. Default "position".
    'scope' => ['group_id'], // Array of field names to use for grouping records. Default [].
    'start' => 1, // Initial value for sequence. Default 1.
]);
```

Now whenever to add a new record its `position` field will be automatically
set to current largest value in sequence plus one.

When editing records you can set the position to a new value and the position of
other records in the list will be automatically updated to maintain proper
sequence.

When doing a find on the table an order clause is automatically added to the
query to order by the position field if a order clause has not already been set.

### Methods

#### moveUp(\Cake\Datasource\EntityInterface $entity)
Move up record by one position:

```php
$modelObject->moveUp($entity);
```

#### moveDown(\Cake\Datasource\EntityInterface $entity)
Move down record by one position:

```php
$modelObject->moveDown($entity);
```

#### setOrder(array $record)
Set order for list of records provided. Records can be provided as array of
entities or array of associative arrays like `[['id' => 1], ['id' => 2]]` or
array of primary key values like `[1, 2]`.

## Acknowledgement

Shout out to @neilcrookes for his wonderful Sequence Behavior for CakePHP 1.3
which was the inspiration for this plugin.
