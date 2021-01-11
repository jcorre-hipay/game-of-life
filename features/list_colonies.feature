Feature: List the colonies

  In order to administrate the application's data
  As a user
  I need to be able to list all the existing colonies

  Business rules:
    - Each colony is displayed at least as its ID and its last generation


  Scenario: Colony listing

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [ ][*][ ]   [ ][*][*]   [ ][*][*]
      [ ][*][*] > [ ][ ][*] > [ ][ ][*]
      [*][*][ ]   [*][ ][*]   [ ][*][ ]
    """
    And there is the colony "4aea4bdb-c789-4945-8086-54bf22561c27":
    """
      [ ][*][*][ ]
      [*][ ][ ][*]
      [ ][*][*][ ]
    """
    When I go to "/colony"
    Then I should see "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I should see "4aea4bdb-c789-4945-8086-54bf22561c27"


  Scenario: Empty listing

    When I go to "/colony"
    Then I should see "No colonies exist yet."
