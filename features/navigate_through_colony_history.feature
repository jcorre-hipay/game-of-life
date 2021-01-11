Feature: Navigate through a colony history

  In order to replay a game of the game of life
  As a user
  I need to be able to access to any generation of a colony


  Background:

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [ ][*][ ]   [ ][*][*]   [ ][*][*]
      [ ][*][*] > [ ][ ][*] > [ ][ ][*]
      [*][*][ ]   [*][ ][*]   [ ][*][ ]
    """


  Scenario: History navigation backward

    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I follow "previous"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [ ][*][*]
      [ ][ ][*]
      [*][ ][*]
    """


  Scenario: Navigation to the first generation

    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I follow "previous"
    And I follow "previous"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 0:
    """
      [ ][*][ ]
      [ ][*][*]
      [*][*][ ]
    """
    And I should not see "previous"


  Scenario: History navigation forward

    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I follow "previous"
    And I follow "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 2:
    """
      [ ][*][*]
      [ ][ ][*]
      [ ][*][ ]
    """
