#!/usr/bin/python3
#
# The Square-Sum Problem Solver (All Paths Version)
# Author: Radim Kocman
# 
# This script tries to solve The Square-Sum Problem for all runs 1-*.
# This problem was introduced by Matt Parker in the Numberphile videos:
# The Square-Sum Problem [https://youtu.be/G1m7goLCJDY]
# The Square-Sum Problem (extra footage) [https://youtu.be/7_ph5djCCnM]
# 
# In short:
# It checks whether runs of numbers can be organized into sequences 
# where every consecutive pair of numbers adds to a square.
# 
# Approach:
# This is a solution without any mathematical libraries.
# It tries to find all possible answers for every tested run.
# To achieve that it tracks all possible paths in a graph for a given run.
# The following runs are always based on the results of the previous ones.
# 
# Results:
# Due to the explosion of all possible paths in a graph around the run 1-27,
# it is very hard to obtain any further answers for longer runs.
#

import math
import copy

class Vertex:
  def __init__(self, id):
    self.id = id
    
  def getId(self):
    return self.id


class Path:
  def __init__(self, vertex):
    self.start = vertex
    self.end = vertex
    self.vertices = {vertex.getId():vertex}
    self.path = [vertex]
    self.length = Path.countLength(self.path)
    
  def getStart(self):
    return self.start
    
  def getEnd(self):
    return self.end
    
  def getLength(self):
    return self.length
    
  def countLength(path):
    return len(path)-1
    
  def getPathVertices(self):
    return self.path
    
  def hasVertex(self, vertex):
    if vertex.getId() in self.vertices:
      return True
    else:
      return False
    
  def hasVertices(self, vertices):
    for vertex in vertices:
      if self.hasVertex(vertex):
        return True
    return False
    
  def mergePaths(firstPath, secondPath):
    newPath = copy.deepcopy(firstPath)
    newPath.end = secondPath.end
    newPath.appendVertices(secondPath.vertices)
    newPath.path = firstPath.path + secondPath.path
    newPath.length = Path.countLength(newPath.path)
    return newPath
    
  def appendVertices(self, vertices):
    for vertex in vertices.values():
      self.vertices[vertex.getId()] = vertex
      
  def __str__(self):
    path = "|"
    for vertex in self.path:
      decorator = ("->", "")[vertex == self.path[-1]]
      path += str(vertex.getId()) + decorator
    path += "|"
    return path
    

class SquareSumSolver:
  def __init__(self):
    self.i = 0;
    self.paths = []
    self.newAppendedPaths = []
    self.newExtendedPaths = []
    print("The Square-Sum Problem Solver - All Paths")
    print("-----------------------------------------")
    
  def solveNext(self):
    self.i = self.i + 1
    print("Case 1-"+str(self.i)+":")
    vertex = Vertex(self.i)
    self.expandPaths(vertex)
    solutions = self.getSolutions()
    if len(solutions) == 0:
      print("FAIL")
    else:
      print("OK")
      for solution in solutions:
        print(solution)
    print("-----------------------------------------")
  
  def expandPaths(self, vertex):
    connections = self.getVertexConnections(vertex)
    self.appendExistingPaths(vertex, connections)
    self.expandAppendedPaths(vertex, connections)
    self.mergeAllPaths()
    
  def getVertexConnections(self, vertex):
    connections = {}
    for i in range(1, vertex.getId()):
      sqrtSum = math.sqrt(i+vertex.getId())
      if sqrtSum == math.floor(sqrtSum):
        connections[i] = Vertex(i)
    return connections
    
  def appendExistingPaths(self, vertex, connections):
    self.newAppendedPaths = []
    path = Path(vertex)
    self.newAppendedPaths.append(path)
    for selectedPath in self.paths:
      if self.checkVertexWithConnections(selectedPath.getEnd(), connections):
        self.newAppendedPaths.append( Path.mergePaths(selectedPath, path) )
        
  def expandAppendedPaths(self, vertex, connections):
    self.newExtendedPaths = []
    for selectedPath in self.newAppendedPaths:
      maxLength = self.i - selectedPath.getLength() - 2
      for appendedPath in self.paths:
        if appendedPath.getLength() > maxLength:
          continue
        if not self.checkVertexWithConnections(appendedPath.getStart(), connections):
          continue
        if selectedPath.hasVertices(appendedPath.getPathVertices()):
          continue
        self.newExtendedPaths.append( Path.mergePaths(selectedPath, appendedPath) )
        
  def mergeAllPaths(self):
    for path in self.newAppendedPaths:
      self.paths.append(path)
    for path in self.newExtendedPaths:
      self.paths.append(path)
      
  def checkVertexWithConnections(self, vertex, connections):
    if vertex.getId() in connections:
      return True
    else:
      return False 
    
  def getSolutions(self):
    solutions = []
    for path in self.paths:
      if path.getLength() == self.i-1:
        solutions.append(path)
    return solutions
    
if __name__ == '__main__':
  solver = SquareSumSolver()
  while True:
    solver.solveNext()
