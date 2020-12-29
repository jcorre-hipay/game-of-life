Feature: Evolve a colony

  In order to run the game of life
  As a user
  I need to be able to evolve a colony to the next generation

  Business rules:
    - Any live cell with fewer than two live neighbours dies, as if by underpopulation
    - Any live cell with two or three live neighbours lives on to the next generation
    - Any live cell with more than three live neighbours dies, as if by overpopulation
    - Any dead cell with exactly three live neighbours becomes a live cell, as if by reproduction


  Scenario: Birth of a cell

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [ ][ ][*]
      [*][ ][ ]
      [ ][*][ ]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [ ][ ][ ]
      [ ][*][ ]
      [ ][ ][ ]
    """


  Scenario: Death of a cell by underpopulation (no neighbours)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [ ][ ][ ]
      [ ][*][ ]
      [ ][ ][ ]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [ ][ ][ ]
      [ ][ ][ ]
      [ ][ ][ ]
    """


  Scenario: Death of a cell by underpopulation (1 neighbour)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [ ][ ][ ]
      [ ][*][*]
      [ ][ ][ ]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [ ][ ][ ]
      [ ][ ][ ]
      [ ][ ][ ]
    """


  Scenario: Survival of a cell to the next generation (2 neighbours)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [ ][ ][*]
      [ ][*][ ]
      [*][ ][ ]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [ ][ ][ ]
      [ ][*][ ]
      [ ][ ][ ]
    """


  Scenario: Survival of a cell to the next generation (3 neighbours)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [*][ ][ ]
      [ ][*][ ]
      [*][ ][*]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [ ][ ][ ]
      [*][*][ ]
      [ ][*][ ]
    """


  Scenario: Death of a cell by overpopulation (4 neighbours)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [ ][*][ ]
      [ ][*][*]
      [*][ ][*]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [ ][*][*]
      [*][ ][*]
      [ ][ ][*]
    """


  Scenario: Death of a cell by overpopulation (5 neighbours)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [*][*][ ]
      [ ][*][*]
      [*][ ][*]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [*][*][*]
      [ ][ ][*]
      [ ][ ][*]
    """


  Scenario: Death of a cell by overpopulation (6 neighbours)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [*][*][ ]
      [ ][*][*]
      [*][*][*]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [*][*][*]
      [ ][ ][ ]
      [*][ ][*]
    """


  Scenario: Death of a cell by overpopulation (7 neighbours)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [*][*][*]
      [ ][*][*]
      [*][*][*]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [*][ ][*]
      [ ][ ][ ]
      [*][ ][*]
    """


  Scenario: Death of a cell by overpopulation (8 neighbours)

    Given there is the colony "59494a9a-32cc-481e-a4f1-093a8dcef162":
    """
      [*][*][*]
      [*][*][*]
      [*][*][*]
    """
    When I go to "/colony"
    And I follow "59494a9a-32cc-481e-a4f1-093a8dcef162"
    And I press "next"
    Then I should see the colony "59494a9a-32cc-481e-a4f1-093a8dcef162" at generation 1:
    """
      [*][ ][*]
      [ ][ ][ ]
      [*][ ][*]
    """
