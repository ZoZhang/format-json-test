# Format JSON Data to CSV
This is a multi-dimensional JSON data conversion based on Symfony, using [ParseCsv](parsecsv) class to export csv file.

[parsecsv]: http://en.wikipedia.org/wiki/Comma-separated_values

## Requirements
| Title | Version |
|----------------------|------------------|
| PHP      | \>=7.2.5   |
| Symfony  | \^5.4.3   |
| ParseCsv | \^1.3.2   |

## Installation

### Git
```
git clone git@github.com:ZoZhang/format-json-test.git
```

## Utilisation
First, go to the program root directory and enter the following command:

```
php bin/console app:export-csv
```

If all goes well, you will get 2 csv files in the `resource` directory, and get a message similar to the following:

![](docs/msg.jpg)
