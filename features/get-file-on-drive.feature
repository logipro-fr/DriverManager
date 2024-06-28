    Feature: Get a file
        
        @getFile
        Scenario: Find a file in a drive
            Given the identity of a file
            When I get the file on its drive
            Then the file has a content