[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/linkorb/gitlog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/linkorb/gitlog/?branch=master)
gitlog
======

Mange change logs from the git commit messages

## Commands
There are 2 executable commands:

### bin/console gitlog:branch [path_to_repo]

Shows the branches of the repo.

### bin/console gitlog:commit [path_to_repo]

Shows or exports the logs. The command supports the following parameters:
* --limit=[(int)limit]: By default it shows/exports only the last commit. Use this paremeter to target on more commits.
* --start=[(int)start]: Starting offset of the commits.
* --format=[array|json|md|console]: The format of the export. By default, the export is shown to console. If md format is selected, it creates a directory in the target repo named "gitlog" and save the extracted/structured comments into it.

### Writing log messages

For gitlog to extract useful information out of the commit messages, the commit message needs to follow a set of simple rules:
* Starting from the 3rd line of the commit message body with keyword: "gitlog" to trigger gitlog to find information.
* On the 4rd line state the type or category of the commit, e.g. dev, production-cn. The types/categories can be delimited with comma ",". Each type/category will have its own directory when exporting to md format. The commit information will be duplicated into every type/category.
* Starting from the 4th line to write the information. Here is an exmaple of commit message.

```
A test commit message body.

gitlog: dev,calendar-bundle-en
The calendar now supports customizing color on group calendars.
```
