Feature: Delete a colony

  In order to clean up the data storage
  As a user
  I need to be able to delete a colony


  Scenario: Colony deletion

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [ ][*][ ]
      [ ][*][*]
      [*][*][ ]
    """
    When I go to "/colony"
    And I press "delete"
    Then I should not see "59494a9a-32cc-481e-a4f1-093a8dcef162"
