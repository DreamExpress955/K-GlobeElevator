// Functions for the machine
#include "machine.h"

int currentFloor = 1;
MachineState state = DoorsClosed;

void Menu ()
{
    std::cout << "Hello please choose from below:" << std::endl;
    std::cout << "1)Request car from floor" << std::endl;
    std::cout << "2)Inside car selection" << std::endl;
    int opt = 0;
    std::cin >> opt;
    switch (opt)
    {
    case 1:
        FloorReq();
        break;

    case 2:
        InsideReq();
        break;
    
    default:
        std::cout << "Error" << std::endl;
        break;
    }
}

void FloorReq ()
{
    std::cout << "What floor are you on?" << std::endl;
    int TargetFloor = 0;
    std::cin >> TargetFloor;
    PrintState(state);
    std::cout << "Moving to target floor: " << std::endl;
    std::cout << TargetFloor << std::endl;
    state = CarMoving;
    PrintState(state);
    currentFloor = TargetFloor;
    std::cout << "Arrived at floor: " << std::endl;
    std::cout << currentFloor << std::endl;
    state = CarStopped;
    PrintState(state);
    state = DoorsOpened;
    PrintState(state);
    std::cout << "Get in car " << std::endl;
}

void InsideReq ()
{

}

void PrintState (MachineState state)
{
        switch (state)
        {
        case DoorsClosed:
           std::cout << "Doors have closed" << std::endl;
        break;

        case DoorsOpened:
        std::cout << "Doors have opened" << std::endl;
        break;

        case CarMoving:
        std::cout << "Car is moving" << std::endl;
        break;

        case CarStopped:
           std::cout << "Car has stopped" << std::endl;
        break;
  
        default:
           std::cout << "Error" << std::endl;
        break;
        }
}