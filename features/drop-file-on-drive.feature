Feature: Drop a file
    
    Scenario: Drop a file in a specific directory
        When I deposit a file "rapport.pdf" in the directory "/Rapports"
        Then the file "rapport.pdf" should be listed in the "/Rapports" directory