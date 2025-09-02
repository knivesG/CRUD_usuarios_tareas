import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { TareaService } from '../../services/tarea.service';
import { UsuarioService } from '../../services/usuario.service';
import { Tarea } from '../../models/tarea.model';
import { Usuario } from '../../models/usuario.model';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.html',
  styleUrls: ['./dashboard.css']
})
export class DashboardComponent implements OnInit {
  totalUsers = 0;
  totalTasks = 0;
  pendingTasks = 0;
  completedTasks = 0;
  recentTasks: Tarea[] = [];
  loading = true;

  constructor(
    private taskService: TareaService,
    private userService: UsuarioService
  ) {}

  ngOnInit() {
    this.loadDashboardData();
  }

  loadDashboardData() {
    this.loading = true;

    // Cargar usuarios
    this.userService.getUsers().subscribe({
      next: (users) => {
        this.totalUsers = users.length;
      },
      error: (error) => {
        console.error('Error al cargar usuarios:', error);
      }
    });

    // Cargar tareas
    this.taskService.getTasks().subscribe({
      next: (tasks) => {
        this.totalTasks = tasks.length;
        this.pendingTasks = tasks.filter(t => t.estado === 'pendiente').length;
        this.completedTasks = tasks.filter(t => t.estado === 'completada').length;
        
        // Obtener las 5 tareas más recientes
        this.recentTasks = tasks
          .sort((a, b) => {
            const dateA = new Date(a.entrega_fecha || '').getTime();
            const dateB = new Date(b.entrega_fecha || '').getTime();
            return dateB - dateA;
          })
          .slice(0, 5);
        
        this.loading = false;
      },
      error: (error) => {
        console.error('Error al cargar tareas:', error);
        this.loading = false;
      }
    });
  }

  getStatusLabel(status: string): string {
    const labels: { [key: string]: string } = {
      'pendiente': 'Pendiente',
      'en_progreso': 'En Progreso',
      'completada': 'Completada'
    };
    return labels[status] || status;
  }
}