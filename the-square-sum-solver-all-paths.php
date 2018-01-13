<?php

/**
 * The Square-Sum Problem Solver (All Paths Version)
 * Author: Radim Kocman
 * 
 * This script tries to solve The Square-Sum Problem for all runs from 1 to *.
 * This problem was introduced by Matt Parker in the Numberphile videos:
 * The Square-Sum Problem [https://youtu.be/G1m7goLCJDY]
 * The Square-Sum Problem (extra footage) [https://youtu.be/7_ph5djCCnM]
 * 
 * In short:
 * It checks whether runs of numbers can be organized into sequences 
 * where every consecutive pair of numbers adds to a square.
 * 
 * Approach:
 * This is a solution without any mathematical libraries.
 * It tries to find all possible answers for every tested run.
 * To achieve that it tracks all possible paths in a graph for a given run.
 * The following runs are always based on the results of the previous ones.
 * 
 * Results:
 * Due to the explosion of all possible paths in a graph around the run 1-27
 * it is very hard to obtain any further answers for longer runs.
 */

ini_set("memory_limit", -1);

class Vertex {
  private $id;
  
  public function __construct($id) {
    $this->id = $id; 
  }
  
  public function getId() {
    return $this->id;
  }
}

class Path {
  private $start;
  private $end;
  private $vertices = [];
  private $path = [];
  private $length;
  
  public function __construct(Vertex $vertex) {
    $this->start = $vertex;
    $this->end = $vertex;
    $this->vertices[$vertex->getId()] = $vertex;
    $this->path[] = $vertex;
    $this->length = $this->countLength();
  }
  
  public function getStart() {
    return $this->start;
  }
  
  public function getEnd() {
    return $this->end;
  }
  
  public function getLength() {
    return $this->length;
  }
  
  private function countLength() {
    return $this->length = count($this->path)-1;
  }
  
  public function getPathVertices() {
    return $this->path;
  }
  
  public function hasVertex(Vertex $vertex) {
    return isset($this->vertices[$vertex->getId()]);
  }
  
  public function hasVertices(array $vertices) {
    foreach ($vertices as $vertex) {
      if ($this->hasVertex($vertex))
        return true;
    }
    return false;
  }
  
  public static function mergePaths(Path $firstPath, Path $secondPath) {
    $newPath = clone $firstPath;
    $newPath->end = $secondPath->end;
    $newPath->appendVertices($secondPath->vertices);
    $newPath->path = array_merge($firstPath->path, $secondPath->path);
    $newPath->length = $newPath->countLength();
    return $newPath;
  }
  
  private function appendVertices(array $vertices) {
    foreach ($vertices as $vertex) {
      $this->vertices[$vertex->getId()] = $vertex;
    }
  }
  
  public function __toString() {
    $path = "|";
    foreach ($this->path as $vertex) {
      $decorator = ($vertex === end($this->path))? "" : "->";
      $path .= $vertex->getId() . $decorator;
    }
    $path .= "|";
    return $path;
  }
}

class SquareSumSolver {
  private $i = 0;
  private $paths = [];
  private $newAppendedPaths = [];
  private $newExtendedPaths = [];
  
  public function __construct() {
    echo "The Square-Sum Problem Solver - All Paths\n";
    echo "-----------------------------------------\n";
  }
  
  public function solveNext() {
    $this->i++;
    echo "Case 1-".$this->i.":\n";
    $vertex = new Vertex($this->i);
    $this->expandPaths($vertex);
    $solutions = $this->getSolutions();
    if (empty($solutions)) {
      echo "FAIL\n";
    } else {
      echo "OK\n";
      foreach ($solutions as $solution) {
        echo $solution."\n";
      }
    }
    echo "-----------------------------------------\n";
  }
  
  private function expandPaths(Vertex $vertex) {
    $connections = $this->getVertexConnections($vertex);
    $this->appendExistingPaths($vertex, $connections);
    $this->expandAppendedPaths($connections);
    $this->mergeAllPaths();
  }
  
  private function getVertexConnections(Vertex $vertex) {
    $connections = [];
    for ($i = 1; $i < $vertex->getId(); $i++) {
      $sqrtSum = sqrt($i+$vertex->getId());
      if ($sqrtSum == floor($sqrtSum))
        $connections[$i] = new Vertex($i);
    }
    return $connections;
  }
  
  private function appendExistingPaths(Vertex $vertex, array $connections) {
    $this->newAppendedPaths = [];
    $path = new Path($vertex);
    $this->newAppendedPaths[] = $path;
    foreach ($this->paths as $selectedPath) {
      if ($this->checkVertexWithConnections($selectedPath->getEnd(), $connections))
        $this->newAppendedPaths[] = Path::mergePaths($selectedPath, $path);
    }
  }
  
  private function expandAppendedPaths(array $connections) {
    $this->newExtendedPaths = [];
    foreach ($this->newAppendedPaths as $selectedPath) {
      $maxLength = $this->i - $selectedPath->getLength() - 2; 
      foreach ($this->paths as $appendedPath) {
        if ($appendedPath->getLength() > $maxLength)
          continue;
        if (!$this->checkVertexWithConnections($appendedPath->getStart(), $connections))
          continue;
        if ($selectedPath->hasVertices($appendedPath->getPathVertices()))
          continue;
        $this->newExtendedPaths[] = Path::mergePaths($selectedPath, $appendedPath);
      }
    }
  }
  
  private function mergeAllPaths() {
    foreach ($this->newAppendedPaths as $path) {
      $this->paths[] = $path;
    }
    foreach ($this->newExtendedPaths as $path) {
      $this->paths[] = $path;
    }
  }
  
  private function checkVertexWithConnections(Vertex $vertex, array $connections) {
    return isset($connections[$vertex->getId()]);
  }
  
  private function getSolutions() {
    $solutions = [];
    foreach ($this->paths as $path) {
      if ($path->getLength() === $this->i - 1)
        $solutions[] = $path;
    }
    return $solutions;
  }
}

$solver = new SquareSumSolver();
while (1) {
  $solver->solveNext();
}
