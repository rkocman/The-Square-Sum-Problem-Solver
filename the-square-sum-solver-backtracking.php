<?php

/**
 * The Square-Sum Problem Solver (Backtracking Version)
 * Author: Radim Kocman
 * 
 * This script tries to solve The Square-Sum Problem for all runs 1-*.
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
 * It uses backtracking to find an answer for a tested run.
 * Single runs are independent of each other (unlike in the all paths version).
 * However, the script uses several simple heuristics to utilize the previous result:
 * - It tries to trivially expand the previous path with a new vertex.
 * - It fast-forwards backtracking to the state where a new vertex can occur. 
 * 
 * Results:
 * It is relatively easy to get answers up to the run 1-75.
 * But unless some heuristic hits, the follow-up results are very slow.
 */

ini_set("memory_limit", -1);

class Vertex {
  private $id;
  private $connections = [];
  
  public function __construct($id) {
    $this->id = $id; 
  }
  
  public function getId() {
    return $this->id;
  }
  
  public function getConnections() {
    return $this->connections;
  }
  
  public function addConnection(Vertex $vertex) {
    $this->connections[$vertex->getId()] = $vertex;
  }
  
  public function hasConnection(Vertex $vertex) {
    return isset($this->connections[$vertex->getId()]);
  }
}

class Path {
  private $start = null;
  private $end = null;
  private $vertices = [];
  private $path = [];
  
  public function getStart() {
    return $this->start;
  }
  
  public function getEnd() {
    return $this->end;
  }
  
  public function getPathVertices() {
    return $this->path;
  }
  
  public function hasVertex(Vertex $vertex) {
    return isset($this->vertices[$vertex->getId()]);
  }
  
  public function hasVertices(array $vertices) {
    foreach ($vertices as $vertex) {
      if ($this->hasVertex($vertex)) {
        return true;
      }
    }
    return false;
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
  
  public function appendEnd(Vertex $vertex) {
    $this->end = $vertex;
    if ($this->start === null) {
      $this->start = $vertex;
    }
    $this->vertices[$vertex->getId()] = $vertex;
    $this->path[] = $vertex;
  }
  
  public function removeEnd() {
    $lastVertex = array_pop($this->path);
    $secondLastVertex = end($this->path);
    unset($this->vertices[$lastVertex->getId()]);
    if ($secondLastVertex === false) {
      $this->end = null;
      $this->start = null;
    } else {
      $this->end = $secondLastVertex;
    }
  }
  
  public function appendAfter(Vertex $newVertex, Vertex $afterVertex = null) {
    $newPath = [];
    if ($afterVertex === null) {
      $newPath[] = $newVertex;
      $this->start = $newVertex;
    }
    if ($afterVertex === end($this->path)) {
      $this->end = $newVertex;
    }
    foreach ($this->path as $vertex) {
      $newPath[] = $vertex;
      if ($vertex === $afterVertex) {
        $newPath[] = $newVertex;
      }
    }
    $this->path = $newPath;
    $this->vertices[$newVertex->getId()] = $newVertex;
  }
}

class SquareSumSolver {
  private $i = 0;
  private $vertices = [];
  private $lastPath = null;

  public function __construct() {
    echo "The Square-Sum Problem Solver - Backtracking\n";
    echo "--------------------------------------------\n";
  }
  
  public function solveNext() {
    $this->i++;
    echo "Case 1-".$this->i.":\n";
    $vertex = new Vertex($this->i);
    ExpandSquareSumVertices::addVertex($this->vertices, $vertex);
    $solution = $this->findSolution($vertex);
    if ($solution === null) {
      echo "FAIL\n";
    } else {
      echo $solution."\n";
    }
    echo "--------------------------------------------\n";
  }
  
  private function findSolution() {
    $solution;
    if ($this->lastPath !== null) {
      $solution = ExpandPathStrategy::solve(end($this->vertices), $this->lastPath);
      if ($solution !== null) {
        $this->lastPath = $solution;
        echo "OK - Expanding\n";
        return $solution;
      }
    }
    $solution = BacktrackingPathStrategy::solve(
      $this->vertices, $this->lastPath);
    $this->lastPath = $solution;
    if ($solution !== null) {
      echo "OK - Backtracking\n";
    }
    return $solution;
  }
}

class ExpandSquareSumVertices {
  public static function addVertex(array &$vertices, Vertex $vertex) {
    $vertices[$vertex->getId()] = $vertex;
    self::createConnections($vertices, $vertex);
  }
  
  private static function createConnections(array &$vertices, Vertex $vertex) {
    $id = $vertex->getId();
    for ($i = 1; $i < $id; $i++) {
      $sqrtSum = sqrt($i+$id);
      if ($sqrtSum == floor($sqrtSum)) {        
        $to = $vertices[$i];
        $to->addConnection($vertex);
        $vertex->addConnection($to);
      }
    }
  }
}

class ExpandPathStrategy {
  public static function solve(Vertex $vertex, Path $lastPath) {
    $vertices = $lastPath->getPathVertices();
    $countOfVertices = count($vertices);
    if ($vertex->hasConnection($lastPath->getStart())) {
      $lastPath->appendAfter($vertex, null);
      return $lastPath;
    }
    if ($vertex->hasConnection($lastPath->getEnd())) {
      $lastPath->appendEnd($vertex);
      return $lastPath;
    }
    for ($i = 0; $i < $countOfVertices-1; $i++) {
      if (
        $vertex->hasConnection($vertices[$i]) &&
        $vertex->hasConnection($vertices[$i+1])
      ) {
        $lastPath->appendAfter($vertex, $vertices[$i]);
        return $lastPath;
      }
    }
    return null;
  }
}

class BacktrackingPathStrategy {
  public static function solve(array &$vertices, Path $lastPath = null) {
    $path = $lastPath;
    if ($path === null) {
      $path = new Path();
    } else {
      $path = self::pathPrecutting($vertices, $path);
    }
    return self::backtracking($vertices, $path);
  }
  
  private static function backtracking(array &$vertices, Path $path) {
    $result = $path;
    $countOfVertices = count($vertices);
    while ($result !== null) {
      if (count($result->getPathVertices()) === $countOfVertices) {
        return $result;
      }
      $result = self::nextPossiblePath($vertices, $path);
      echo $result."\n";
    }
    return null;
  }
  
  private static function nextPossiblePath(array &$vertices, Path $path) {
    if (count($path->getPathVertices()) === 0) {
      $path->appendEnd($vertices[1]);
      return $path;
    }
    if (count($path->getPathVertices()) < count($vertices)) {
      $result = self::nextExpandedPath($path);
      if ($result !== null) {
        return $result;
      }
    }
    while (count($path->getPathVertices()) >= 2) {
      $result = self::nextFollowingPath($path);
      if ($result !== null) {
        return $result;
      }
    }
    if ($path->getEnd()->getId() < count($vertices)) {
      $next = $vertices[$path->getEnd()->getId()+1];
      $path->removeEnd();
      $path->appendEnd($next);
      return $path;
    }
    return null;
  }
  
  private static function nextExpandedPath(Path $path) {
    $current = $path->getEnd();
    $connections = $current->getConnections();
    foreach ($connections as $connection) {
      if ($path->hasVertex($connection)) {
        continue;
      }
      $path->appendEnd($connection);
      return $path;
    }
    return null;
  }
  
  private static function nextFollowingPath(Path $path) {
    $last = $path->getEnd();
    $path->removeEnd();
    $current = $path->getEnd();
    $connections = $current->getConnections();
    $passedLast = false;
    foreach ($connections as $connection) {
      if ($connection === $last) {
        $passedLast = true;
        continue;
      }
      if (!$passedLast) {
        continue;
      }
      if ($path->hasVertex($connection)) {
        continue;
      }
      $path->appendEnd($connection);
      return $path;
    }
    return null;
  }
  
  private static function pathPrecutting(array &$vertices, Path $path) {
    $lastVertex = end($vertices);
    $oneSkipped = false;
    while (count($path->getPathVertices()) >= 1) {
      if ($lastVertex->hasConnection($path->getEnd())) {
        if (!$oneSkipped) {
          $oneSkipped = true;
        } else {
          $path->appendEnd($lastVertex);
          return $path;
        }
      }
      $path->removeEnd();
    }
    $path->appendEnd($lastVertex);
    return $path;
  }
}

$solver = new SquareSumSolver();
while (1) {
  $solver->solveNext();
}