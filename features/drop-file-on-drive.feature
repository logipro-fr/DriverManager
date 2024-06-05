Feature: Drop a file
    
    Scenario Outline: Drop a file in a specific directory
        Given the drive is <drive>
        And the directory <directory> exists <doesExist>
        When I deposit a file "rapport.pdf" in the directory <directory>
        Then the file "rapport.pdf" should be listed in the directory <directory>
        And the file has its own identity
        And the file has create and updated dates
        And the file knows its fullname path 

        Examples:
            |     drive     |        directory     | doesExist |
            | NextCloudMock | "/Rapports"          |   "yes"   |
            |  FileSysteme  | "/NextSign"          |    "no"   |
            | NextCloudMock | "/NextSign/archived" |    "no"   |
